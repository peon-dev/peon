import './styles/app.scss';

// start the Stimulus application
import './bootstrap';

require('prismjs');
require('prismjs/components/prism-diff');
require('prismjs/themes/prism.css');

require('bootstrap');
require('@fortawesome/fontawesome-free/css/all.min.css');
require('@fortawesome/fontawesome-free/js/all.js');

const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))

tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
})
