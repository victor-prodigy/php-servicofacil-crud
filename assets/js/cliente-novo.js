document.getElementById("btnNovoCliente").addEventListener("click", function () {
  clienteNovo();
});

// criar cliente, fetch funcao async
async function clienteNovo() {
  try {
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

    // Verifica se a resposta HTTP foi bem-sucedida
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    // Verifica se a resposta é JSON válido
    const responseText = await response.text();
    console.log("Resposta do servidor:", responseText); // Para debug

    let result;
    try {
      result = JSON.parse(responseText);
    } catch (e) {
      throw new Error("Resposta do servidor não é JSON válido: " + responseText);
    }

    var msg = "";

    if (result.codigo == true) {
      msg = "<span class='alert alert-success'>" + result.msg + "</span>";
      // Limpa o formulário após sucesso
      document.getElementById("formNovoCliente").reset();
    } else {
      msg = "<span class='alert alert-danger'>" + result.msg + "</span>";
    }
    document.getElementById("alerta").innerHTML = msg;
    // if (result.status === "success") {
    //   document.getElementById("alerta").innerHTML = `<div class="alert alert-success" role="alert">
    //   ${result.message}
    // </div>`;
    // } else {
    //   document.getElementById("alerta").innerHTML = `<div class="alert alert-danger" role="alert">  
    //   ${result.message}
    // </div>`;
    // }

  } catch (error) {
    console.error("Erro:", error);
    document.getElementById("alerta").innerHTML =
      "<span class='alert alert-danger'>Erro de conexão: " + error.message + "</span>";
  }
}


// document.getElementById("btnNovoCliente").addEventListener("click", function () {
//   clienteNovo();
// });

// // criar cliente, fetch funcao async
// async function clienteNovo() {
//   // const fd = new FormData(document.getElementById("formNovoCliente")); // body
//   const fd = new FormData(); // body

//   // adiciona campos manualmente no FormData
//   fd.append("email", document.getElementById("email").value);
//   fd.append("senha", document.getElementById("pwd").value);

//   // await nao deixar codigo continuar ate a Promise nao for retornada
//   const response = await fetch("../app/cliente-novo.php", {
//     method: "POST",
//     body: fd,
//   });

//   const result = await response.json();
//   // console.log(result);

//   var msg = "";

//   if (result.codigo == true) {
//     msg = "<span class='alert alert-success'>" + result.msg + "</span>";
//   } else {
//     msg = "<span class='alert alert-danger'>" + result.msg + "</span>";
//   }
//   document.getElementById("alerta").innerHTML = msg;
//   // if (result.status === "success") {
//   //   document.getElementById("alerta").innerHTML = `<div class="alert alert-success" role="alert">
//   //   ${result.message}
//   // </div>`;
//   // } else {
//   //   document.getElementById("alerta").innerHTML = `<div class="alert alert-danger" role="alert">
//   //   ${result.message}
//   // </div>`;
//   // }
// }
