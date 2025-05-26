function mostrarFestejados() {
  fetch('../controllers/festejados_controller.php')
    .then(response => response.json())
    .then(data => {
      if (data.length > 0) {
        const modal = document.getElementById("cumpleanosModal");
        const span = modal.querySelector(".close");
        const cumpleanosTexto = document.getElementById("cumpleanosTexto");

        // Corrige el acceso a las claves correctas: user_name y user_surname
        const message = "🎉 Hoy cumplen años: " + 
          data.map(user => `${user.user_name} ${user.user_surname}`).join(", ");

        cumpleanosTexto.textContent = message;
        modal.style.display = "block";

        span.onclick = () => modal.style.display = "none";

        window.onclick = (event) => {
          if (event.target === modal) {
            modal.style.display = "none";
          }
        };
      }
    })
    .catch(error => console.error('Error fetching users with birthday:', error));
}

document.addEventListener('DOMContentLoaded', mostrarFestejados);
