document.getElementById("btnNovoCliente").addEventListener("click", () => {
  // route do cliente-novo.html
  window.location.href = "../client/cliente-novo.html";
});

// NOTE: DOM depois que carregar toda pagina ele carrega ele executa a funcao clienteListar
document.addEventListener("DOMContentLoaded", () => {
  clienteListar();
});

// criar cliente, fetch funcao async
async function clienteListar() {
  try {
    // Fetch para buscar a lista de clientes
    const response = await fetch("../app/cliente-listar.php");

    // Verifica se a resposta HTTP foi bem-sucedida
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    // Parse do JSON
    const data = await response.json();
    console.log("Dados recebidos:", data);

    var table = `<table class="table table-striped">
      <thead>
        <tr>
          <th>ID</th>
          <th>Email</th>
          <th>Data Cadastro</th>
          <th>Ações</th>
        </tr>
      </thead>
     <tbody>
    `;

    // Verifica se há dados na resposta
    if (data.codigo && data.lista && data.lista.length > 0) {
      for (var i = 0; i < data.lista.length; i++) {
        var linha = data.lista[i];

        table += `
          <tr>
            <td>${linha.id}</td>
            <td>${linha.email}</td>
            <td>${linha.data_cadastro || 'N/A'}</td>
            <td>
              <button class="btn btn-sm btn-warning" onclick="editarCliente(${linha.id})">Editar</button>
              <button class="btn btn-sm btn-danger" onclick="excluirCliente(${linha.id})">Excluir</button>
            </td>
            </tr>
        `;
      }
    } else {
      table += `
        <tr>
          <td colspan="4" class="text-center">Nenhum cliente encontrado</td>
        </tr>
      `;
    }

    table += `</tbody>`;
    table += `</table>`;

    document.getElementById("lista").innerHTML = table;
  } catch (error) {
    console.error("Erro:", error);
    document.getElementById("alerta").innerHTML =
      "<span class='alert alert-danger'>Erro de conexão: " + error.message + "</span>";
  }
}

// TODO: colocar em cliente-editar.js
// Função para editar cliente
function editarCliente(id) {
  window.location.href = `../client/cliente-editar.html?id=${id}`;
}

// TODO: colocar em cliente-excluir.js
// Função para excluir cliente
async function excluirCliente(id) {
  if (confirm("Tem certeza que deseja excluir este cliente?")) {
    try {
      const response = await fetch("../app/cliente-excluir.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `id=${id}`
      });

      const data = await response.json();

      if (data.codigo) {
        alert("Cliente excluído com sucesso!");
        clienteListar(); // Recarrega a lista
      } else {
        alert("Erro ao excluir cliente: " + data.msg);
      }
    } catch (error) {
      alert("Erro de conexão: " + error.message);
    }
  }
}
