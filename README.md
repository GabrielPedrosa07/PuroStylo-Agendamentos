# ✂️ Puro Stylo - Sistema de Agendamentos e Gestão

> **Sistema completo para gestão de salão de beleza e barbearia em Muriaé.**

![Banner do Projeto](images/banner_placeholder.jpg)
<!-- (Sugestão: Adicione um print da tela inicial aqui nomeado como banner_placeholder.jpg) -->

## 📖 Sobre o Projeto

O **Puro Stylo - Agendamentos** é uma solução web robusta desenvolvida para modernizar e facilitar a gestão do salão **Puro Stylo**. O sistema oferece uma plataforma intuitiva para que clientes realizem agendamentos online, comprem produtos e acompanhem o blog do salão, enquanto fornece aos administradores ferramentas poderosas para controle financeiro, gestão de estoque, comissões de funcionários e muito mais.

---

## 🚀 Funcionalidades Principais

### 🌟 Área do Cliente (Site Público)
*   **Agendamento Online**: Interface fácil para escolha de serviços, profissionais e horários.
*   **Vitrine de Produtos**: Catálogo de produtos com opção de compra/carrinho.
*   **Blog**: Dicas, novidades e notícias do mundo da beleza.
*   **Galeria de Profissionais**: Perfil detalhado da equipe.
*   **Depoimentos**: Espaço para feedback e avaliações de clientes.
*   **Painel do Cliente**: Área logada para visualizar histórico e futuros agendamentos.

### ⚙️ Painel Administrativo
*   **Dashboard Intuitivo**: Visão geral dos agendamentos e finanças do dia/mês.
*   **Gestão de Agenda**: Controle total dos horários, bloqueios e reagendamentos.
*   **Financeiro Completo**:
    *   Contas a Pagar e Receber.
    *   Fluxo de Caixa (Entradas e Saídas).
    *   Calculo Automático de Comissões.
    *   Vendas e Compras.
*   **Cadastros**:
    *   Clientes e Fornecedores.
    *   Funcionários com controle de cargos e acessos.
    *   Serviços e Categorias.
    *   Produtos e Estoque.
*   **Relatórios**: Geração de relatórios detalhados para tomada de decisão.

---

## 🛠️ Tecnologias Utilizadas

O projeto foi construído utilizando tecnologias modernas e eficientes:

*   **Backend**: PHP 7/8 (PDO para segurança nas querys).
*   **Frontend**: HTML5, CSS3, JavaScript.
*   **Framework CSS**: Bootstrap (Design responsivo e mobile-first).
*   **Bibliotecas JS**: jQuery, DataTables (Tabelas dinâmicas), Owl Carousel (Sliders), Mask (Máscaras de input).
*   **Banco de Dados**: MySQL.
*   **Servidor**: Apache (Compatível com WAMP/XAMPP).

---

## 📦 Estrutura de Pastas

```
PuroStylo-Agendamentos/
├── ajax/                # Scripts de processamento assíncrono
├── BANCO INICIAL/       # Script SQL para criação do banco de dados
├── css/ & js/           # Estilos e Scripts do site público
├── images/              # Imagens do layout e uploads
├── sistema/             # Núcleo do sistema administrativo
│   ├── painel/          # Área restrita do administrador/funcionário
│   │   ├── paginas/     # Módulos do sistema (Clientes, Agenda, etc)
│   │   └── rel/         # Geradores de relatórios
│   └── conexao.php      # Configuração de banco de dados
├── index.php            # Página inicial do site
└── ...outros arquivos principais
```

---

## 🔧 Instalação e Configuração

Siga os passos abaixo para rodar o projeto em seu ambiente local:

1.  **Pré-requisitos**: Tenha instalado um servidor local como [WAMP](http://www.wampserver.com/en/) ou [XAMPP](https://www.apachefriends.org/index.html).
2.  **Clone o Repositório**:
    ```bash
    git clone https://github.com/GabrielPedrosa07/PuroStylo-Agendamentos.git
    ```
3.  **Configurar Banco de Dados**:
    *   Acesse o PHPMyAdmin (geralmente `http://localhost/phpmyadmin`).
    *   Crie um banco de dados (ex: `purostylo`).
    *   Importe o arquivo `.sql` localizado na pasta `BANCO INICIAL`.
4.  **Conexão**:
    *   Abra o arquivo `sistema/conexao.php`.
    *   Ajuste as credenciais (`host`, `usuario`, `senha`, `banco`) conforme seu ambiente.
5.  **Acessar**:
    *   Abra o navegador e acesse `http://localhost/PuroStylo-Agendamentos`.

---

## 📸 Screenshots

| Tela Inicial | Agendamento | Painel Admin |
|:---:|:---:|:---:|
| *(Insira print)* | *(Insira print)* | *(Insira print)* |

---

## 📞 Contato e Suporte

Desenvolvido para **Sullamita - Puro Stylo**.

*   📍 Localização: Muriaé
*   📧 Suporte Técnico: [Seu Email Aqui]

---
*Feito com ❤️ por Gabriel Pedrosa.*
