document.addEventListener('DOMContentLoaded', function() {
  fetch('../controllers/festejados_controller.php')
    .then(response => response.json())
    .then(data => {
      if (data.length > 0) {
        const modal = document.getElementById("cumpleanosModal");
        const span = document.getElementsByClassName("close")[0];
        const cumpleanosTexto = document.getElementById("cumpleanosTexto");
        
        let message = "Hoy cumplen años: ";
        data.forEach((user, index) => {
          message += `${user.name} ${user.surname}`;
          if (index < data.length - 1) {
            message += ", ";
          }
        });
        cumpleanosTexto.textContent = message;

        modal.style.display = "block";

        span.onclick = function() {
          modal.style.display = "none";
        }

        window.onclick = function(event) {
          if (event.target == modal) {
            modal.style.display = "none";
          }
        }
      }
    })
    .catch(error => console.error('Error fetching users with birthday:', error));
});
