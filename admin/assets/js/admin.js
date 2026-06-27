/**
 * RC Slopes — Interface d'administration
 * JS commun à toutes les pages.
 */

document.addEventListener('DOMContentLoaded', function () {
  initSidebarToggle();
  initDeleteConfirmations();
  initTinyMCEFields();
  initImageDropzones();
});

/* -------------------------------------------------------------------- */
/* Sidebar off-canvas en mobile                                         */
/* -------------------------------------------------------------------- */
function initSidebarToggle() {
  const toggleBtn = document.getElementById('rcsSidebarToggle');
  const sidebar = document.getElementById('rcsSidebar');
  const backdrop = document.getElementById('rcsSidebarBackdrop');

  if (!toggleBtn || !sidebar || !backdrop) return;

  function openSidebar() {
    sidebar.classList.add('is-open');
    backdrop.classList.add('is-open');
  }
  function closeSidebar() {
    sidebar.classList.remove('is-open');
    backdrop.classList.remove('is-open');
  }

  toggleBtn.addEventListener('click', function () {
    sidebar.classList.contains('is-open') ? closeSidebar() : openSidebar();
  });
  backdrop.addEventListener('click', closeSidebar);

  // Ferme la sidebar après un clic sur un lien (mobile)
  sidebar.querySelectorAll('a.nav-link').forEach(function (link) {
    link.addEventListener('click', closeSidebar);
  });
}

/* -------------------------------------------------------------------- */
/* Confirmation avant suppression (formulaires avec data-confirm)       */
/* -------------------------------------------------------------------- */
function initDeleteConfirmations() {
  document.querySelectorAll('form[data-confirm]').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      const msg = form.getAttribute('data-confirm') || 'Confirmer cette action ?';
      if (!window.confirm(msg)) {
        e.preventDefault();
      }
    });
  });
}

/* -------------------------------------------------------------------- */
/* Initialisation TinyMCE sur les champs .rcs-wysiwyg                   */
/* -------------------------------------------------------------------- */
function initTinyMCEFields() {
  if (typeof tinymce === 'undefined') return;

  const fields = document.querySelectorAll('textarea.rcs-wysiwyg');
  if (!fields.length) return;

  tinymce.init({
    selector: 'textarea.rcs-wysiwyg',
    height: 420,
    menubar: false,
    branding: false,
    language: 'fr_FR',
    language_url: '/admin/assets/vendor/tinymce/langs/fr_FR.js',
    plugins: 'lists link image table code searchreplace autolink fullscreen media',
    image_class_list: [
      { title: 'Fluid', value: 'img-fluid' }
    ],
    image_description: false,
    image_dimensions: false,
    toolbar:
      'undo redo | blocks | bold italic underline strikethrough | ' +
      'forecolor backcolor | alignleft aligncenter alignright | ' +
      'bullist numlist | link image table | code fullscreen',
    relative_urls: false,
    remove_script_host: false,
    convert_urls: false,

    // Upload d'image directement depuis TinyMCE (collage, glisser-déposer, bouton image)
    images_upload_url: '/admin/upload_image.php',
    automatic_uploads: true,
    file_picker_types: 'image',

    images_upload_handler: function (blobInfo) {
      return new Promise(function (resolve, reject) {
        const formData = new FormData();
        formData.append('image', blobInfo.blob(), blobInfo.filename());
        formData.append('csrf_token', window.RCS_CSRF_TOKEN || '');

        fetch('/admin/upload_image.php', {
          method: 'POST',
          body: formData,
          credentials: 'same-origin',
        })
          .then(function (response) {
            if (!response.ok) throw new Error('Erreur serveur (' + response.status + ')');
            return response.json();
          })
          .then(function (data) {
            if (data.success) {
              resolve(data.url);
            } else {
              reject(data.message || "Échec de l'upload.");
            }
          })
          .catch(function (err) {
            reject('Erreur réseau : ' + err.message);
          });
      });
    },

    // Sélecteur de fichier custom pour le bouton "Image" -> ouvre la médiathèque interne
    file_picker_callback: function (callback, value, meta) {
      if (meta.filetype === 'image') {
        openMediaLibrary(function (url) {
          callback(url, { alt: '' });
        });
      }
    },
  });
}

/* -------------------------------------------------------------------- */
/* Médiathèque modale (réutilise la liste d'images existantes)          */
/* -------------------------------------------------------------------- */
function openMediaLibrary(onSelect) {
  const modalEl = document.getElementById('rcsMediaLibraryModal');
  if (!modalEl) {
    // Pas de médiathèque sur cette page : on retombe sur l'upload direct du navigateur
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/*';
    input.onchange = function () {
      if (!input.files || !input.files[0]) return;
      const formData = new FormData();
      formData.append('image', input.files[0]);
      formData.append('csrf_token', window.RCS_CSRF_TOKEN || '');
      fetch('/admin/upload_image.php', { method: 'POST', body: formData, credentials: 'same-origin' })
        .then((r) => r.json())
        .then((data) => {
          if (data.success) onSelect(data.url);
          else alert(data.message);
        });
    };
    input.click();
    return;
  }

  const modal = new bootstrap.Modal(modalEl);
  modalEl.dataset.selectCallbackActive = '1';
  window._rcsMediaLibraryCallback = onSelect;
  modal.show();
}

/* -------------------------------------------------------------------- */
/* Dropzone d'upload (page gestion des images)                          */
/* -------------------------------------------------------------------- */
function initImageDropzones() {
  document.querySelectorAll('.rcs-image-dropzone').forEach(function (zone) {
    const input = zone.querySelector('input[type="file"]');
    if (!input) return;

    zone.addEventListener('click', function (e) {
      if (e.target.tagName !== 'INPUT' && e.target.tagName !== 'BUTTON') {
        input.click();
      }
    });

    ['dragenter', 'dragover'].forEach(function (evt) {
      zone.addEventListener(evt, function (e) {
        e.preventDefault();
        zone.classList.add('is-dragover');
      });
    });

    ['dragleave', 'drop'].forEach(function (evt) {
      zone.addEventListener(evt, function (e) {
        e.preventDefault();
        zone.classList.remove('is-dragover');
      });
    });

    zone.addEventListener('drop', function (e) {
      const files = e.dataTransfer.files;
      if (files && files.length) {
        input.files = files;
        const form = zone.closest('form');
        if (form) form.requestSubmit();
      }
    });

    input.addEventListener('change', function () {
      if (input.files.length) {
        const form = zone.closest('form');
        if (form) form.requestSubmit();
      }
    });
  });
}
