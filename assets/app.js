import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.scss';

document.addEventListener('DOMContentLoaded', () => {

    // Get all "navbar-burger" elements
    const $navbarBurgers = Array.prototype.slice.call(document.querySelectorAll('.navbar-burger'), 0);
  
    // Add a click event on each of them
    $navbarBurgers.forEach( el => {
      el.addEventListener('click', () => {
  
        // Get the target from the "data-target" attribute
        const target = el.dataset.target;
        const $target = document.getElementById(target);
  
        // Toggle the "is-active" class on both the "navbar-burger" and the "navbar-menu"
        el.classList.toggle('is-active');
        $target.classList.toggle('is-active');
  
      });
    });
  
});


// Gestion du champ d'envoi de fichier des formulaire pour que le nom du fichier apparaisse une fois sélectionné
const fileInput = document.querySelector(".file input[type=file]");

if(fileInput) {
  fileInput.onchange = () => {

      if (fileInput.files.length > 0) {

        const fileName = document.querySelector(".file .file-name");
        fileName.textContent = fileInput.files[0].name;
      }
    };
}