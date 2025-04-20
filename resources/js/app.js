/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
require('datatables.net-bs4');
import $ from 'jquery';

window.$ = window.jQuery = $;
import 'datatables.net-bs4/js/dataTables.bootstrap4.min.js';
import 'datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js';
import 'datatables.net-fixedcolumns-bs4/js/fixedColumns.bootstrap4.min.js';



// import 'swiper/swiper-bundle.min.js';
import Swiper from 'swiper/bundle';
import 'swiper/swiper-bundle.css';




window.moment = require('moment');
window.daterangepicker = require('daterangepicker');
window.datepicker = require('bootstrap-datepicker');
window.select2 = require('select2');
window.toastr = require('toastr');
window.Swal = require('sweetalert2');
window.bsCustomFileInput = require('admin-lte/plugins/bs-custom-file-input/bs-custom-file-input.min');
window.chart = require('chart.js/dist/Chart.min.js');

require('jquery-autocomplete');
require('multiple-select');
window.autoComplete = require('pixabay-javascript-autocomplete');
window.interact = require('interactjs');
window.GLightbox  = require('glightbox');
window.AOS = require('aos');
/**
 window.toastr = require('toastr');
 window.Swal = require('sweetalert2');

 window.select2 = require('select2');
 /**

 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))


/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */
