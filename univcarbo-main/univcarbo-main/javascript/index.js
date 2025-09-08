document.addEventListener("DOMContentLoaded", function() {
    setTimeout(function() {
        document.getElementById('logo-screen').style.display = 'none';
        document.getElementById('main-content').classList.add('show');
        document.body.style.overflow = 'auto'; 
    },1000); 
});
