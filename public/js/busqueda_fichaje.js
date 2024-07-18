function buscarFichaje() {
  var input, filter, table, tr, td, i, j, txtValue;
  input = document.getElementById("busqueda_fichaje");
  filter = input.value.toUpperCase();
  table = document.getElementById("tabla-fichajes");
  tr = table.getElementsByTagName("tr");

  // Recorre todas las filas de la tabla y oculta las que no coincidan con la búsqueda
  for (i = 1; i < tr.length; i++) {
    tr[i].style.display = "none";
    td = tr[i].getElementsByTagName("td");
    for (j = 0; j < td.length; j++) {
      if (td[j]) {
        txtValue = td[j].textContent || td[j].innerText;
        if (txtValue.toUpperCase().indexOf(filter) > -1) {
          tr[i].style.display = "";
          break;
        }
      }
    }
  }
}