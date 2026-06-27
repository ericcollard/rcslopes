<?php
/**
 * Nettoyage du HTML produit par TinyMCE avant stockage en base.
 *
 * Objectif : conserver la mise en forme légitime (gras, listes, liens, images,
 * couleurs en style inline simples...) tout en éliminant tout vecteur XSS
 * (scripts, gestionnaires d'événements on*, javascript: URIs, iframes, etc.)
 *
 * Implémentation volontairement dépendance-free (DOMDocument, natif PHP) :
 * si le projet a accès à composer/HTMLPurifier, on peut le substituer ici
 * pour un nettoyage encore plus robuste — voir note en bas de fichier.
 */

final class HtmlSanitizer
{
    /** Balises autorisées */
    private const ALLOWED_TAGS = [
        'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'span', 'div',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'ul', 'ol', 'li',
        'a', 'img',
        'table', 'thead', 'tbody', 'tr', 'th', 'td',
        'blockquote', 'pre', 'code', 'hr',
    ];

    /** Attributs autorisés, par balise ('*' = toutes balises autorisées ci-dessus) */
    private const ALLOWED_ATTRS = [
        '*'   => ['style', 'class', 'id'],
        'a'   => ['href', 'title', 'target', 'rel'],
        'img' => ['src', 'alt', 'width', 'height', 'loading'],
        'td'  => ['colspan', 'rowspan'],
        'th'  => ['colspan', 'rowspan'],
    ];

    /** Propriétés CSS autorisées dans un attribut style="" */
    private const ALLOWED_STYLE_PROPS = [
        'color', 'background', 'background-color', 'text-align',
        'font-weight', 'font-style', 'text-decoration', 'padding',
        'margin', 'border', 'border-radius', 'width', 'height', 'float',
    ];

    public static function clean(string $html): string
    {
        $html = trim($html);
        if ($html === '') {
            return '';
        }

        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->formatOutput = false;

        // On encapsule pour permettre un fragment HTML valide
        $wrapped = '<?xml encoding="utf-8" ?><div id="rcs-root">' . $html . '</div>';

        $prevErrorSetting = libxml_use_internal_errors(true);
        $doc->loadHTML($wrapped, LIBXML_NOWARNING | LIBXML_NOERROR | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($prevErrorSetting);

        $root = $doc->getElementById('rcs-root');
        if ($root === null) {
            return '';
        }

        self::cleanNode($doc, $root);

        $output = '';
        foreach (iterator_to_array($root->childNodes) as $child) {
            $output .= $doc->saveHTML($child);
        }

        return trim($output);
    }

    private static function cleanNode(DOMDocument $doc, DOMNode $node): void
    {
        $children = iterator_to_array($node->childNodes);

        foreach ($children as $child) {
            if ($child->nodeType === XML_TEXT_NODE) {
                continue;
            }

            if ($child->nodeType === XML_COMMENT_NODE) {
                $node->removeChild($child);
                continue;
            }

            if ($child->nodeType !== XML_ELEMENT_NODE) {
                $node->removeChild($child);
                continue;
            }

            /** @var DOMElement $child */
            $tagName = strtolower($child->tagName);

            if (!in_array($tagName, self::ALLOWED_TAGS, true)) {
                // Balise interdite (script, iframe, object, style, form...) :
                // on déplace ses enfants texte au niveau parent puis on la supprime.
                self::unwrapDisallowedTag($doc, $node, $child);
                continue;
            }

            self::cleanAttributes($child, $tagName);
            self::cleanNode($doc, $child); // récursif sur les enfants
        }
    }

    private static function unwrapDisallowedTag(DOMDocument $doc, DOMNode $parent, DOMElement $el): void
    {
        // Pour des balises dangereuses comme <script> ou <style>, on supprime tout (contenu inclus).
        $dangerous = ['script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'button', 'link', 'meta'];
        if (in_array(strtolower($el->tagName), $dangerous, true)) {
            $parent->removeChild($el);
            return;
        }

        // Sinon, on garde le texte/les enfants mais on retire la balise elle-même.
        while ($el->firstChild) {
            $parent->insertBefore($el->firstChild, $el);
        }
        $parent->removeChild($el);
    }

    private static function cleanAttributes(DOMElement $el, string $tagName): void
    {
        $allowed = array_merge(self::ALLOWED_ATTRS['*'], self::ALLOWED_ATTRS[$tagName] ?? []);

        $attrs = iterator_to_array($el->attributes ?? []);
        foreach ($attrs as $attr) {
            $name = strtolower($attr->name);

            // Bloque systématiquement tout gestionnaire d'événement (onclick, onerror, ...)
            if (str_starts_with($name, 'on') || !in_array($name, $allowed, true)) {
                $el->removeAttribute($attr->name);
                continue;
            }

            if ($name === 'href' || $name === 'src') {
                $safeValue = self::sanitizeUrl($attr->value);
                if ($safeValue === null) {
                    $el->removeAttribute($attr->name);
                } else {
                    $el->setAttribute($attr->name, $safeValue);
                }
                continue;
            }

            if ($name === 'style') {
                $el->setAttribute('style', self::sanitizeStyle($attr->value));
                continue;
            }
        }

        // Sécurité supplémentaire sur les liens externes
        if ($tagName === 'a' && $el->getAttribute('target') === '_blank') {
            $el->setAttribute('rel', 'noopener noreferrer');
        }
    }

    private static function sanitizeUrl(string $url): ?string
    {
        $url = trim($url);

        // data:image/... autorisé UNIQUEMENT pour les images collées/uploadées via TinyMCE,
        // avec un format strictement validé (évite tout détournement du schéma data:).
        if (preg_match('/^data:image\/(png|jpe?g|gif|webp);base64,[A-Za-z0-9+\/=]+$/i', $url)) {
            return $url;
        }

        // Rejette tout autre schéma dangereux (javascript:, data: non-image, vbscript:, etc.)
        if (preg_match('/^\s*(javascript|data|vbscript):/i', $url)) {
            return null;
        }

        // Autorise : relatif, http(s), mailto, tel, ancre
        if (preg_match('/^(https?:)?\/\//i', $url)
            || preg_match('/^(mailto|tel):/i', $url)
            || preg_match('/^#/', $url)
            || preg_match('/^\//', $url)
            || preg_match('/^[\w\-.\/]+$/', $url) // chemin relatif simple
        ) {
            return $url;
        }

        return null;
    }

    private static function sanitizeStyle(string $style): string
    {
        $safeDeclarations = [];
        foreach (explode(';', $style) as $decl) {
            if (!str_contains($decl, ':')) {
                continue;
            }
            [$prop, $value] = array_map('trim', explode(':', $decl, 2));
            $prop = strtolower($prop);

            if (!in_array($prop, self::ALLOWED_STYLE_PROPS, true)) {
                continue;
            }
            // Bloque toute tentative d'injection via url(javascript:...) ou expression()
            if (preg_match('/(javascript:|expression\(|url\(\s*[\'"]?\s*javascript:)/i', $value)) {
                continue;
            }

            $safeDeclarations[] = "{$prop}: {$value}";
        }

        return implode('; ', $safeDeclarations);
    }
}

/*
 * NOTE — Montée en robustesse optionnelle :
 * Si l'environnement de production dispose de Composer, il est recommandé de
 * remplacer cette classe par la bibliothèque éprouvée "ezyang/htmlpurifier" :
 *
 *   composer require ezyang/htmlpurifier
 *
 *   $config = HTMLPurifier_Config::createDefault();
 *   $config->set('HTML.Allowed', 'p,br,strong,b,em,i,u,span[style|class],...');
 *   $purifier = new HTMLPurifier($config);
 *   $clean = $purifier->purify($html);
 *
 * La classe ci-dessus reste une protection sérieuse et autonome (sans dépendance),
 * mais HTMLPurifier est plus exhaustivement testé contre les vecteurs XSS exotiques.
 */
