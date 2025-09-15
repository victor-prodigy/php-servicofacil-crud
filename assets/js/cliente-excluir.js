async function clienteExcluir(id) {
  const response = await fetch("../app/cliente-excluir.php");
  const data = await response.json();

  alert(data.msg);

  window.location.href = "../client/cliente-listar.html";
}