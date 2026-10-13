## Amar Assist

A API usa tokens Bearer do Laravel Sanctum. Registre ou autentique um usuário
em `POST /api/register` ou `POST /api/login`; envie o token retornado como
`Authorization: Bearer <token>` nas rotas protegidas.

- `GET /api/dashboard`: receitas, despesas e saldo do usuário autenticado.
- `GET /api/transactions`: lista paginada das transações do usuário.
- `POST /api/transactions`: cria transação com tipo `income`/`expense` ou
  `Receita`/`Despesa`.
- `POST /api/imports`: recebe o CSV no campo `file` e retorna `202`; consulte
  o progresso em `GET /api/imports/{id}`.

O CSV deve conter `date,description,amount,type`, com datas `AAAA-MM-DD` e
tipos `Receita` ou `Despesa`. `amount` é positivo, aceita até duas casas
decimais e representa o valor monetário; os valores são armazenados como
centavos inteiros. Linhas inválidas são relatadas no estado da importação.

Execute os testes com `php artisan test` em `backend/`. A suíte usa SQLite em
memória, sem depender do MySQL ou Redis.
