document.getElementById("btnNovoCliente").addEventListener("click", function () {
  clienteNovo();
});

// criar cliente, fetch funcao async
async function clienteNovo() {
  // const fd = new FormData(document.getElementById("formNovoCliente")); // body
  const fd = new FormData(); // body

  // adiciona campos manualmente no FormData
  fd.append("email", document.getElementById("email").value);
  fd.append("senha", document.getElementById("pwd").value);

  // await nao deixar codigo continuar ate a Promise nao for retornada
  const response = await fetch("../app/cliente-novo.php", {
    method: "POST",
    body: fd,
  });

  const result = await response.json();
  console.log(result);
}
