<?php
/**
 * Registre déclaratif des tables administrables.
 *
 * Chaque entrée décrit une table de la base `rcslopes` telle qu'utilisée
 * par le CRUD générique : clé primaire, colonnes, types de champ pour le
 * formulaire, libellés FR, et règles de validation simples.
 *
 * Types de champ supportés par le formulaire généré :
 *   text, textarea, wysiwyg, number, decimal, checkbox, select, datetime, date, hidden
 */

final class TableRegistry
{
    /**
     * @return array<string, array>
     */
    public static function all(): array
    {
        return [

            // -----------------------------------------------------------
            // Table : slopes
            // -----------------------------------------------------------
            'slopes' => [
                'label'       => 'Sites de vol (Slopes)',
                'icon'        => 'bi-geo-alt-fill',
                'primary_key' => 'slopeId',
                'pk_auto'     => false, // PAS d'AUTO_INCREMENT : géré manuellement (MAX+1)
                'order_by'    => 'slopeId DESC',
                'list_columns' => ['slopeId', 'name', 'country', 'dpt', 'type', 'updated_at'],
                'search_columns' => ['name', 'description', 'country', 'dpt', 'addBy'],
                'columns' => [
                    'slopeId' => [
                        'label' => 'ID', 'type' => 'hidden', 'editable' => false,
                    ],
                    'name' => [
                        'label' => 'Nom du site', 'type' => 'text', 'required' => true, 'maxlength' => 255,
                    ],
                    'lat' => [
                        'label' => 'Latitude', 'type' => 'decimal', 'step' => '0.000001', 'required' => false,
                    ],
                    'lng' => [
                        'label' => 'Longitude', 'type' => 'decimal', 'step' => '0.000001', 'required' => false,
                    ],
                    'orient' => [
                        'label' => 'Orientations (vent)', 'type' => 'select_multiple',
                        'options' => ['N','NNE','NE','ENE','E','ESE','SE','SSE','S','SSW','SW','WSW','W','WNW','NW','NNW'],
                    ],
                    'type' => [
                        'label' => 'Type de site', 'type' => 'select',
                        'options' => ['pente' => 'Pente', 'interdit' => 'Interdit', 'treuil' => 'Treuil', 'autre' => 'Autre'],
                        'allow_custom' => true,
                    ],
                    'description' => [
                        'label' => 'Description (FR)', 'type' => 'wysiwyg',
                    ],
                    'weather_url' => [
                        'label' => 'URL météo', 'type' => 'text', 'maxlength' => 2048,
                    ],
                    'addBy' => [
                        'label' => 'Ajouté par', 'type' => 'text', 'maxlength' => 255,
                    ],
                    'club' => [
                        'label' => 'Site de club', 'type' => 'checkbox',
                    ],
                    'cotisation' => [
                        'label' => 'Cotisation requise', 'type' => 'checkbox',
                    ],
                    'licence' => [
                        'label' => 'Licence requise', 'type' => 'checkbox',
                    ],
                    'url' => [
                        'label' => 'Lien externe (texte libre)', 'type' => 'textarea',
                    ],
                    'aip' => [
                        'label' => 'Référence AIP', 'type' => 'text', 'maxlength' => 255,
                    ],
                    'desc_en' => [
                        'label' => 'Description (EN)', 'type' => 'wysiwyg',
                    ],
                    'country' => [
                        'label' => 'Pays', 'type' => 'text', 'maxlength' => 100,
                    ],
                    'dpt' => [
                        'label' => 'Département', 'type' => 'text', 'maxlength' => 100,
                    ],
                    'created_at' => [
                        'label' => 'Créé le', 'type' => 'datetime', 'editable' => false, 'auto_on_create' => true,
                    ],
                    'updated_at' => [
                        'label' => 'Mis à jour le', 'type' => 'datetime', 'editable' => false, 'auto_always' => true,
                    ],
                ],
            ],

            // -----------------------------------------------------------
            // Table : weather_forecast
            // -----------------------------------------------------------
            'weather_forecast' => [
                'label'       => 'Prévisions météo',
                'icon'        => 'bi-cloud-sun-fill',
                'primary_key' => 'forecast_id',
                'pk_auto'     => true,
                'order_by'    => 'forecast_time DESC',
                'list_columns' => ['forecast_id', 'slope_id', 'forecast_time', 'wind_speed', 'wind_gust', 'temperature'],
                'search_columns' => [],
                'columns' => [
                    'forecast_id' => [
                        'label' => 'ID', 'type' => 'hidden', 'editable' => false,
                    ],
                    'slope_id' => [
                        'label' => 'Site (slopeId)', 'type' => 'lookup', 'required' => true,
                        'lookup_table' => 'slopes', 'lookup_pk' => 'slopeId', 'lookup_label' => 'name',
                    ],
                    'wind_heading' => [
                        'label' => 'Direction du vent (°)', 'type' => 'decimal', 'step' => '0.01',
                    ],
                    'wind_speed' => [
                        'label' => 'Vitesse du vent (km/h)', 'type' => 'decimal', 'step' => '0.01',
                    ],
                    'wind_gust' => [
                        'label' => 'Rafales (km/h)', 'type' => 'decimal', 'step' => '0.01',
                    ],
                    'cloud_cover' => [
                        'label' => 'Couverture nuageuse (%)', 'type' => 'decimal', 'step' => '0.01',
                    ],
                    'rain' => [
                        'label' => 'Précipitations (mm)', 'type' => 'decimal', 'step' => '0.01',
                    ],
                    'temperature' => [
                        'label' => 'Température (°C)', 'type' => 'decimal', 'step' => '0.01',
                    ],
                    'forecast_time' => [
                        'label' => 'Date de prévision', 'type' => 'datetime', 'required' => true,
                    ],
                    'created_at' => [
                        'label' => 'Créé le', 'type' => 'datetime', 'editable' => false, 'auto_on_create' => true,
                    ],
                    'updated_at' => [
                        'label' => 'Mis à jour le', 'type' => 'datetime', 'editable' => false, 'auto_always' => true,
                    ],
                ],
            ],

            // -----------------------------------------------------------
            // Table : wind_station
            // -----------------------------------------------------------
            'wind_station' => [
                'label'       => 'Stations de vent',
                'icon'        => 'bi-broadcast',
                'primary_key' => 'id',
                'pk_auto'     => true,
                'order_by'    => 'updated_at DESC',
                'list_columns' => ['id', 'station_id', 'provider', 'measurement_date', 'wind_speed_avg', 'updated_at'],
                'search_columns' => ['provider'],
                'columns' => [
                    'id' => [
                        'label' => 'ID', 'type' => 'hidden', 'editable' => false,
                    ],
                    'station_id' => [
                        'label' => 'ID Station (externe)', 'type' => 'number', 'required' => true,
                    ],
                    'provider' => [
                        'label' => 'Fournisseur', 'type' => 'text', 'required' => true, 'maxlength' => 255,
                    ],
                    'widget_code' => [
                        'label' => 'Code Widget (HTML)', 'type' => 'wysiwyg',
                    ],
                    'latitude' => [
                        'label' => 'Latitude', 'type' => 'decimal', 'step' => '0.00000001',
                    ],
                    'longitude' => [
                        'label' => 'Longitude', 'type' => 'decimal', 'step' => '0.00000001',
                    ],
                    'measurement_date' => [
                        'label' => 'Date de mesure', 'type' => 'datetime',
                    ],
                    'wind_heading' => [
                        'label' => 'Direction du vent (°)', 'type' => 'decimal', 'step' => '0.01',
                    ],
                    'wind_speed_avg' => [
                        'label' => 'Vitesse moy. (km/h)', 'type' => 'decimal', 'step' => '0.01',
                    ],
                    'wind_speed_max' => [
                        'label' => 'Vitesse max. (km/h)', 'type' => 'decimal', 'step' => '0.01',
                    ],
                    'wind_speed_min' => [
                        'label' => 'Vitesse min. (km/h)', 'type' => 'decimal', 'step' => '0.01',
                    ],
                    'created_at' => [
                        'label' => 'Créé le', 'type' => 'datetime', 'editable' => false, 'auto_on_create' => true,
                    ],
                    'updated_at' => [
                        'label' => 'Mis à jour le', 'type' => 'datetime', 'editable' => false, 'auto_always' => true,
                    ],
                ],
            ],

        ];
    }

    public static function get(string $table): ?array
    {
        $all = self::all();
        return $all[$table] ?? null;
    }

    public static function exists(string $table): bool
    {
        return array_key_exists($table, self::all());
    }
}
