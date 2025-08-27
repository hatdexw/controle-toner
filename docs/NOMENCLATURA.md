Mapeamento de nomes de arquivo - projeto controle-toner

Objetivo

- Tornar os nomes dos arquivos `src/pages/` mais claros para desenvolvedores, mantendo as rotas públicas inalteradas.

Alterações aplicadas

- `src/pages/home.php` -> `src/pages/lista_impressoras.php`
  - Responsável por renderizar os cards/lista de impressoras (rota: GET /controle-toner/)
- `src/pages/impressoras.php` -> `src/pages/gerenciar_impressoras.php`
  - Responsável pela tela de gerenciamento (adicionar/editar/excluir) de impressoras (rota: GET/POST /controle-toner/impressoras)

Notas de compatibilidade

- As rotas no `index.php` foram atualizadas para usar os novos nomes de arquivo. Nenhuma URL pública foi alterada.
- Não foram removidos arquivos antigos neste patch para evitar perda acidental. Se desejar, posso remover os arquivos originais após verificação manual.

Como contribuir

- Utilize nomes descritivos em `src/pages/` quando adicionar novas páginas, e atualize `index.php` para mapear as rotas às páginas corretas.
- Se automatizar rename via git, prefira `git mv` para preservar histórico (pode ser feito localmente).
