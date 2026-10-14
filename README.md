# Test Amar

## Ambiente de desenvolvimento

### Pré-requisitos

O projeto utiliza Docker Compose para orquestrar os serviços da aplicação. Para executá-lo localmente, é necessário ter o Docker instalado e em execução, além do Git para obter o código-fonte.

Não é necessário instalar as dependências da aplicação diretamente na máquina host, desde que os serviços e suas dependências estejam devidamente configurados nos containers.

### Subindo a aplicação

Na raiz do projeto, execute:

```bash
docker compose -f docker-compose.yml -f docker-compose.dev.yml up -d --build
```

O arquivo `docker-compose.dev.yml` complementa a configuração principal com os ajustes específicos para desenvolvimento local.

Após a inicialização, confira se os containers estão em execução:

```bash
docker compose ps
```

Caso algum serviço não inicialize corretamente, consulte os logs:

```bash
docker compose logs --tail=100
```

Para acompanhar um serviço específico em tempo real, utilize `docker compose logs -f <serviço>`.

O endereço de acesso à aplicação depende das portas configuradas nos arquivos Compose.

## Pontos importantes da implementação

### Prevenção de uploads duplicados

O sistema utiliza uma impressão digital baseada em **SHA-256** para identificar planilhas já enviadas pelo usuário autenticado.

Quando o mesmo usuário tenta enviar novamente uma planilha com uma impressão digital já registrada, a operação é ignorada, evitando o reprocessamento desnecessário do arquivo.

A identificação é baseada no conteúdo do arquivo, e não apenas no nome. Dessa forma, renomear uma planilha não deve permitir um novo envio quando seu conteúdo permanece idêntico.

### Processamento assíncrono e notificações

O processamento das planilhas é desacoplado da requisição de upload e executado por um worker de filas, disponibilizado pelo serviço `queue`.

Isso permite que o usuário continue navegando pela aplicação enquanto o processamento está pendente. Após a conclusão, a interface deve exibir um toast de notificação.

Esse comportamento pode ser validado interrompendo temporariamente o worker e retomando seu funcionamento após o envio de uma planilha.

### Testes automatizados

Os testes unitários são executados pelo **GitHub Actions** exclusivamente no ambiente `develop`, utilizando SQLite como banco de dados.

Essa configuração permite executar os testes sem depender de uma instância externa do banco de dados utilizado nos demais ambientes.

## Validando o fluxo de notificações

O objetivo deste teste é verificar o comportamento da aplicação quando uma planilha aguarda na fila e confirmar se a notificação é exibida após a conclusão do processamento.

### 1. Pause o worker

Execute o comando:

```bash
docker compose stop queue
```

### 2. Envie uma planilha

Com o serviço de filas parado, acesse a aplicação e faça o upload de uma planilha válida.

Como o worker está parado, o processamento deverá permanecer pendente e a barra de progresso continuará em execução.

Durante esse período, navegue para o dashboard ou outra página da aplicação. A navegação deve continuar funcionando normalmente, sem exigir que o usuário permaneça na tela de upload.

### 3. Retome o processamento

Inicie novamente o serviço responsável pelo processamento das filas:

```bash
docker compose start queue
```

Com o worker novamente em execução, a fila deverá ser processada. Ao finalizar o processamento, um toast de notificação deverá ser exibido na interface.

**Resultado esperado:** o upload é registrado, o processamento aguarda a disponibilidade do worker, o usuário pode navegar pela aplicação e a conclusão do processamento é comunicada pela interface.

Caso a notificação não apareça, consulte os logs dos serviços envolvidos para identificar possíveis falhas no processamento ou na comunicação com o frontend:

```bash
docker compose logs --tail=100 queue
```

## Diagrama da aplicação

O diagrama da arquitetura pode ser consultado no GitDiagram:

[Visualizar diagrama da aplicação](https://gitdiagram.com/geovanecodder/test-amar)

Ao acessar o link, clique em **Regenerate** para gerar o diagrama.

## Documentação da API

A especificação dos endpoints está disponível no arquivo [`docs/openapi.yaml`](./docs/openapi.yaml), no formato OpenAPI.

O arquivo documenta os contratos da API e pode ser utilizado por ferramentas compatíveis para consultar os endpoints, seus parâmetros e as estruturas de requisição e resposta.