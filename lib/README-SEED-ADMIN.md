# Seed do Usuário Administrador

Este documento explica como criar o usuário administrador no sistema ServiçoFácil.

## 📋 Credenciais Padrão

- **Email:** `admin@servicofacil.com`
- **Senha:** `admin123`
- **Nome:** Administrador do Sistema
- **Telefone:** (11) 99999-9999

## 🚀 Métodos de Execução

### Método 1: Via Script PHP (Recomendado)

O script PHP gera automaticamente o hash da senha e é mais seguro.

```bash
cd C:\xampp\htdocs\php-servicofacil-crud-incompleto\php
php seed-admin.php
```

**Vantagens:**
- Gera hash da senha automaticamente
- Verifica se o administrador já existe
- Permite atualizar o administrador existente
- Exibe informações de acesso após a execução

### Método 2: Via SQL Direto

Execute o arquivo SQL diretamente no MySQL/MariaDB:

```sql
-- No MySQL/MariaDB
source C:/xampp/htdocs/php-servicofacil-crud-incompleto/lib/seed-admin.sql;
```

Ou importe via phpMyAdmin:
1. Acesse phpMyAdmin
2. Selecione o banco `servicofacil`
3. Vá em "Importar"
4. Selecione o arquivo `lib/seed-admin.sql`
5. Clique em "Executar"

## 📝 Estrutura do Seed

O seed cria um usuário com as seguintes características:

- **user_type:** `administrador`
- **status:** `ativo`
- **identity_verified:** `TRUE`
- **password:** Hash bcrypt da senha `admin123`

## 🔐 Segurança

⚠️ **IMPORTANTE:** Após criar o administrador, é recomendado:

1. Alterar a senha padrão através do sistema
2. Usar uma senha forte
3. Não compartilhar as credenciais

## 🔗 URLs de Acesso

Após criar o administrador, você pode acessar:

- **Login:** `http://localhost/php-servicofacil-crud-incompleto/client/login/administrador-signin.html`
- **Dashboard:** `http://localhost/php-servicofacil-crud-incompleto/client/administrador-dashboard.html`

## ✅ Verificação

Para verificar se o administrador foi criado corretamente:

```sql
SELECT user_id, email, name, user_type, status, created_at 
FROM user 
WHERE user_type = 'administrador';
```

## 🔄 Resetar Administrador

Se precisar resetar o administrador:

### Via PHP:
```bash
php seed-admin.php
# Quando perguntado, responda 's' para atualizar
```

### Via SQL:
```sql
DELETE FROM user WHERE user_type = 'administrador';
-- Depois execute o seed-admin.sql novamente
```

## 📚 Funcionalidades do Administrador

O administrador tem acesso a:

- ✅ Visualizar todos os usuários (clientes e prestadores)
- ✅ Filtrar usuários por tipo, status e busca
- ✅ Ativar/Desativar contas de usuários
- ✅ Excluir usuários (com validações)
- ✅ Ver detalhes completos dos usuários
- ✅ Visualizar estatísticas do sistema
- ✅ Gerenciar serviços
- ✅ Ver histórico de atividades

## 🐛 Troubleshooting

### Erro: "Tabela 'user' não encontrada"
Execute primeiro o script de criação de tabelas:
```bash
php create-tables.php
```

### Erro: "Coluna 'user_type' não encontrada"
A tabela precisa ser atualizada. Execute:
```sql
ALTER TABLE user ADD COLUMN user_type ENUM('cliente', 'prestador', 'administrador') DEFAULT 'cliente';
ALTER TABLE user ADD COLUMN status ENUM('ativo', 'inativo') DEFAULT 'ativo';
```

### Hash da senha não funciona
Use o script PHP `seed-admin.php` que gera o hash corretamente, ou gere um novo hash:
```php
<?php
echo password_hash('admin123', PASSWORD_DEFAULT);
```

