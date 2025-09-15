async function clienteCarregar(id) {
  const response = await fetch("../app/cliente-carregar.php?id=" + id); // pagina clienteId
  const data = await response.json();

  window.location.href = "../client/cliente-editar.html";
}