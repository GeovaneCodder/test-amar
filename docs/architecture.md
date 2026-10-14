
```text
amar-assist/
├── backend/
│   ├── app/
│   │   ├── Domain/
│   │   │   ├── Identity/
│   │   │   │   └── Exceptions/
│   │   │   ├── Transactions/
│   │   │   │   ├── Entities/
│   │   │   │   ├── Enums/
│   │   │   │   ├── ValueObjects/
│   │   │   │   └── Exceptions/
│   │   │   └── Imports/
│   │   │       ├── Entities/
│   │   │       ├── Enums/
│   │   │       └── Exceptions/
│   │   │
│   │   ├── Application/
│   │   │   ├── Identity/
│   │   │   │   ├── DTOs/
│   │   │   │   └── Actions/
│   │   │   │       ├── RegisterUser.php
│   │   │   │       ├── LoginUser.php
│   │   │   │       └── LogoutUser.php
│   │   │   ├── Transactions/
│   │   │   │   ├── DTOs/
│   │   │   │   ├── Actions/
│   │   │   │   │   ├── ListTransactions.php
│   │   │   │   │   └── CreateTransaction.php
│   │   │   │   └── Contracts/
│   │   │   ├── Imports/
│   │   │   │   ├── DTOs/
│   │   │   │   ├── Actions/
│   │   │   │   │   ├── StartImport.php
│   │   │   │   │   ├── ProcessImportChunk.php
│   │   │   │   │   ├── PauseImport.php
│   │   │   │   │   ├── ResumeImport.php
│   │   │   │   │   └── CancelImport.php
│   │   │   │   └── Contracts/
│   │   │   │       ├── CsvReader.php
│   │   │   │       ├── ImportRepository.php
│   │   │   │       └── FileFingerprint.php
│   │   │   └── Dashboard/
│   │   │       ├── DTOs/
│   │   │       └── Actions/
│   │   │           └── GetDashboardSummary.php
│   │   │
│   │   ├── Infrastructure/
│   │   │   ├── Persistence/
│   │   │   │   └── Eloquent/
│   │   │   │       ├── Models/
│   │   │   │       │   ├── User.php
│   │   │   │       │   ├── Transaction.php
│   │   │   │       │   └── Import.php
│   │   │   │       └── Repositories/
│   │   │   ├── Csv/
│   │   │   │   ├── NativeCsvReader.php
│   │   │   │   └── Sha256FileFingerprint.php
│   │   │   └── Cache/
│   │   │       └── RedisDashboardCache.php
│   │   │
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   │   ├── Auth/
│   │   │   │   ├── Transactions/
│   │   │   │   ├── Imports/
│   │   │   │   └── DashboardController.php
│   │   │   ├── Requests/
│   │   │   │   ├── Auth/
│   │   │   │   ├── StoreTransactionRequest.php
│   │   │   │   └── StoreImportRequest.php
│   │   │   └── Resources/
│   │   │       ├── UserResource.php
│   │   │       ├── TransactionResource.php
│   │   │       └── ImportResource.php
│   │   │
│   │   ├── Jobs/
│   │   │   └── ProcessImportChunkJob.php
│   │   ├── Events/
│   │   │   ├── TransactionsImported.php
│   │   │   └── ImportProgressUpdated.php
│   │   ├── Listeners/
│   │   │   └── InvalidateDashboardCache.php
│   │   ├── Policies/
│   │   │   ├── TransactionPolicy.php
│   │   │   └── ImportPolicy.php
│   │   └── Providers/
│   │       └── AppServiceProvider.php
│   │
│   ├── config/
│   ├── database/
│   │   ├── migrations/
│   │   ├── factories/
│   │   └── seeders/
│   ├── routes/
│   │   ├── api.php
│   │   └── web.php
│   ├── tests/
│   │   ├── Unit/
│   │   │   ├── Transactions/
│   │   │   └── Dashboard/
│   │   └── Feature/
│   │       ├── Auth/
│   │       ├── Imports/
│   │       ├── Transactions/
│   │       └── Dashboard/
│   ├── Dockerfile
│   ├── composer.json
│   └── phpunit.xml
│
├── frontend/
│   ├── src/
│   │   ├── api/
│   │   │   ├── http.ts
│   │   │   ├── auth.ts
│   │   │   ├── transactions.ts
│   │   │   ├── imports.ts
│   │   │   └── dashboard.ts
│   │   ├── components/
│   │   │   ├── AppHeader.vue
│   │   │   ├── TransactionTable.vue
│   │   │   ├── CsvUpload.vue
│   │   │   └── ImportProgress.vue
│   │   ├── views/
│   │   │   ├── LoginView.vue
│   │   │   ├── DashboardView.vue
│   │   │   ├── TransactionsView.vue
│   │   │   └── ImportsView.vue
│   │   ├── router/
│   │   │   └── index.ts
│   │   ├── stores/
│   │   │   └── auth.ts
│   │   └── types/
│   │       ├── transaction.ts
│   │       └── import.ts
│   ├── Dockerfile
│   ├── package.json
│   └── vite.config.ts
│
├── infrastructure/
│   └── nginx/
│       └── default.conf
├── docs/
│   ├── openapi.yaml
│   └── architecture.md
├── .github/
│   └── workflows/
│       └── ci.yml
├── docker-compose.yml
├── .env.example
├── .gitignore
└── README.md
```

## Desenvolvimento local

Para iniciar a aplicação com o HMR do Vite, execute na raiz do projeto:

```sh
docker compose -f docker-compose.yml -f docker-compose.dev.yml up --build
```

O frontend fica disponível na mesma URL do Nginx (`http://localhost`, ou a porta
configurada em `NGINX_PORT`). O override monta `frontend/` no container e ativa
polling de arquivos para detectar alterações no Docker Desktop. Sem o arquivo
de override, o Compose continua usando o frontend estático de produção.