# UrbaZoom API

API REST do projeto UrbaZoom (registro e acompanhamento de ocorrências urbanas), feita com
Java 17, Spring Boot 3.3, Spring Data JPA, banco H2 e Swagger/OpenAPI (springdoc).

## Como executar

Requisitos: Java 17 ou superior e Maven (ou uma IDE que abra projetos Maven: IntelliJ, Eclipse, VS Code, STS).

```
mvn spring-boot:run
```

Na IDE: abra a pasta como projeto Maven e execute a classe `UrbazoomApplication`.

| O quê | Endereço |
|---|---|
| Swagger (documentação e testes) | http://localhost:8080/swagger-ui.html |
| Console do H2 | http://localhost:8080/h2-console |

Console do H2: JDBC URL `jdbc:h2:mem:urbazoom`, usuário `sa`, senha em branco.
O banco é em memória: a cada execução ele é recriado e carregado com os dados de `src/main/resources/data.sql`.

## Entidades

`Usuario`, `Orgao`, `Categoria`, `StatusOcorrencia`, `Ocorrencia`, `HistoricoOcorrencia`, `Evidencia`
(a modelagem é a mesma do banco do UrbaZoom em PHP/MySQL).

## Endpoints

CRUD completo (GET lista, GET /{id}, POST, PUT /{id}, DELETE /{id}) em:
`/usuarios`, `/orgaos`, `/categorias`, `/status`, `/ocorrencias`, `/historicos`, `/evidencias`.

Endpoints especializados:

| Método | Caminho | Função |
|---|---|---|
| GET | /ocorrencias/protocolo/{protocolo} | Busca pelo protocolo (ex.: URB-2038) |
| GET | /ocorrencias/filtro?statusId=&categoriaId=&orgaoId=&prioridade= | Filtro combinado (todos opcionais) |
| GET | /ocorrencias/usuario/{usuarioId} | Ocorrências de um cidadão |
| GET | /ocorrencias/periodo?inicio=2026-01-01&fim=2026-03-31 | Ocorrências abertas no período |
| GET | /ocorrencias/busca?texto= | Busca no título ou na localização |
| PATCH | /ocorrencias/{id}/status | Muda o status e grava no histórico |
| GET | /ocorrencias/relatorio/por-status | Total por status |
| GET | /ocorrencias/relatorio/por-categoria | Total por categoria |
| GET | /ocorrencias/relatorio/por-orgao | Total por órgão |
| GET | /historicos/ocorrencia/{id} | Linha do tempo de uma ocorrência |
| GET | /categorias/orgao/{orgaoId} | Categorias de um órgão |
| GET | /evidencias/ocorrencia/{id} | Evidências de uma ocorrência |
| GET | /usuarios/buscar?email= | Busca usuário pelo e-mail |

### Exemplos de corpo (JSON)

Criar ocorrência (POST /ocorrencias):
```json
{
  "usuario": {"id": 9},
  "categoria": {"id": 1},
  "orgao": {"id": 1},
  "titulo": "Buraco na avenida",
  "descricao": "Buraco grande em frente ao mercado.",
  "localizacao": "Avenida Brasil, 100",
  "prioridade": "ALTA",
  "status": {"id": 1}
}
```

Mudar status (PATCH /ocorrencias/1/status):
```json
{"statusId": 3, "mensagem": "Equipe enviada ao local.", "usuarioId": 2}
```

## Dados de teste

Usuários cadastrados: ids 1, 2, 4, 9, 10 e 11 (o id 2 é o "Servidor Demo"). 11 ocorrências de exemplo.
