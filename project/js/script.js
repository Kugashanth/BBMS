// js/script.js

// Example function for a fade effect on a content area
function loadContent(pageUrl) {
    var content = document.querySelector('.content');
    content.classList.add('fade-out');
    
    setTimeout(function() {
        // Use AJAX (or fetch) to load page content dynamically if needed.
        // This example uses fetch() to get content from the given URL.
        fetch(pageUrl)
          .then(response => response.text())
          .then(data => {
              content.innerHTML = data;
              content.classList.remove('fade-out');
          })
          .catch(error => {
              content.innerHTML = "<p>Error loading page.</p>";
              content.classList.remove('fade-out');
          });
    }, 500); // Duration should match the CSS transition
}

document.addEventListener("DOMContentLoaded", function(){
    // Additional event listeners can be attached here.
});



