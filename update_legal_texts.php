<?php
require_once("sistema/conexao.php");

$termos_uso = "<h3>1. Aceitação dos Termos</h3>
<p>Ao acessar e criar uma conta em nosso sistema de agendamento online, você concorda em cumprir estes Termos de Uso e todas as leis e regulamentos aplicáveis. Se você não concordar com algum destes termos, está proibido de usar ou acessar este site.</p>

<h3>2. Uso do Sistema</h3>
<p>O sistema tem como objetivo facilitar o agendamento de serviços em nosso estabelecimento. Ao agendar um horário, você se compromete a comparecer ou cancelar com antecedência mínima de 24 horas.</p>

<h3>3. Cadastro do Usuário</h3>
<p>Para utilizar o sistema, é necessário realizar um cadastro fornecendo informações verdadeiras e atualizadas. Você é responsável por manter a confidencialidade de sua conta e senha.</p>

<h3>4. Cancelamentos e Atrasos</h3>
<p>Reservamo-nos o direito de cancelar agendamentos em caso de imprevistos dos profissionais. Atrasos superiores a 15 minutos podem implicar na perda do horário agendado, sujeito à disponibilidade.</p>

<h3>5. Propriedade Intelectual</h3>
<p>Todo o conteúdo deste site, incluindo logotipos, textos e imagens, é de propriedade exclusiva do estabelecimento ou de seus licenciadores.</p>

<h3>6. Modificações</h3>
<p>Podemos revisar estes termos de serviço a qualquer momento, sem aviso prévio. Ao usar este site, você concorda em ficar vinculado à versão atual desses termos de serviço.</p>";

$politica_privacidade = "<h3>1. Coleta de Informações</h3>
<p>Coletamos informações pessoais que você nos fornece voluntariamente ao se cadastrar, como agendamentos, nome, número de telefone e endereço de e-mail. Também podemos coletar dados de navegação através de cookies.</p>

<h3>2. Uso das Informações</h3>
<p>Usamos as informações coletadas para:</p>
<ul>
    <li>Gerenciar seus agendamentos e histórico de serviços;</li>
    <li>Enviar confirmações e lembretes de horários (via WhatsApp ou E-mail);</li>
    <li>Melhorar nossos serviços e atendimento;</li>
    <li>Entrar em contato para fins administrativos ou promocionais (se autorizado).</li>
</ul>

<h3>3. Compartilhamento de Dados</h3>
<p>Não vendemos, trocamos ou transferimos suas informações pessoais para terceiros externos, exceto para os profissionais que prestarão o serviço contratado dentro do nosso estabelecimento.</p>

<h3>4. Segurança</h3>
<p>Implementamos medidas de segurança para manter suas informações pessoais protegidas. No entanto, nenhum método de transmissão pela Internet é 100% seguro.</p>

<h3>5. Cookies</h3>
<p>Utilizamos cookies para melhorar a experiência do usuário, manter sua sessão ativa e salvar preferências. Você pode optar por desativar os cookies no seu navegador, mas algumas funcionalidades podem ser limitadas.</p>

<h3>6. Seus Direitos</h3>
<p>Você tem o direito de solicitar o acesso, correção ou exclusão de seus dados pessoais em nosso sistema a qualquer momento, entrando em contato conosco.</p>";

try {
    $query = $pdo->prepare("UPDATE config SET termos_uso = :termos, politica_privacidade = :politica");
    $query->bindValue(":termos", $termos_uso);
    $query->bindValue(":politica", $politica_privacidade);
    $query->execute();
    echo "Textos atualizados com sucesso!";
} catch (Exception $e) {
    echo "Erro ao atualizar: " . $e->getMessage();
}
?>
