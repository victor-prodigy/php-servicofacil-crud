# Database Setup - ServiçoFácil

# Executar php

- criando db mysql usando php
  php criar_db.php

Este diretório contém os arquivos de configuração do banco de dados para o sistema ServiçoFácil.

## Arquivos

### 📁 `db.sql`

Contém a **estrutura completa do banco de dados**:

- Criação das tabelas
- Definição de chaves primárias e estrangeiras
- Índices e constraints
- Relacionamentos entre tabelas

### 📁 `seed-admin.sql`

Contém o **usuário administrador padrão**:

- Email: admin@servicofacil.com
- Senha: admin123
- Execute este arquivo para criar o administrador do sistema

### 📁 `seed.sql`

Contém os **dados de exemplo** para desenvolvimento e testes:

- Usuários de exemplo (clientes e prestadores)
- Clientes e prestadores
- Solicitações de serviços
- Propostas, contratos e pagamentos
- Mensagens de chat e avaliações

## Como usar

### 1. Configurar a estrutura do banco

```sql
-- Execute primeiro o db.sql (apenas estrutura, sem dados)
source lib/db.sql;
```

### 2. Inserir administrador (recomendado)

```sql
-- Execute o seed-admin.sql para criar o usuário administrador
source lib/seed-admin.sql;
```

### 3. Inserir dados de exemplo (opcional)

```sql
-- Execute depois o seed.sql para dados de teste
source lib/seed.sql;
```

## Estrutura do Banco

### Tabelas Principais:

- `user` - Usuários base do sistema
- `customer` - Clientes
- `service_provider` - Prestadores de serviços
- `service_request` - Solicitações de serviços
- `proposal` - Propostas dos prestadores
- `contract` - Contratos firmados
- `payment` - Pagamentos
- `review` - Avaliações
- `chat` - Sistema de mensagens
- `cliente` - Tabela legada (compatibilidade)

### Dados de Exemplo:

- 6 usuários (3 clientes, 3 prestadores)
- 5 solicitações de serviços
- 7 propostas
- 4 contratos
- 5 pagamentos
- 4 avaliações
- 10 mensagens de chat

## Observações

- Execute os arquivos na ordem: `db.sql` → `seed-admin.sql` → `seed.sql`
- O `seed-admin.sql` é recomendado para criar o administrador do sistema
- O `seed.sql` é opcional, use apenas para desenvolvimento/testes
- A tabela `cliente` foi mantida para compatibilidade com código existente
- Todas as senhas são hash da palavra "password" usando bcrypt (exceto admin que usa "admin123")
