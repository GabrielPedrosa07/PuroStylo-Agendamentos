<?php 
$nivel = '../';
require_once("../cabecalho.php");
@session_start();

if(!isset($_SESSION['id_cliente'])){
    echo "<script>window.location='../login-cliente.php'</script>";
    exit();
}

$id_cliente = $_SESSION['id_cliente'];
?>


<style>
    .sub_page .hero_area { min-height: auto; }
</style>
</div>

<div class="layout_padding" style="background: white;">
    <div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Meus Agendamentos</h3>
        <div>
            <a href="editar-perfil.php" class="btn btn-outline-dark mr-2">Editar Perfil</a>
            <a href="../agendamentos.php" class="btn btn-primary" style="background-color: #000; border-color: #000;">Novo Agendamento</a>
        </div>
    </div>

    <div class="alert alert-info">
        <i class="fa fa-info-circle"></i> <strong>Atenção:</strong> O cancelamento de agendamentos deve ser feito solicitando diretamente ao profissional via WhatsApp. Clique no botão "Cancelar" para iniciar a conversa.
    </div>

    <div class="row">
        <?php 
        $query = $pdo->query("SELECT a.id, a.data, a.hora, a.status, s.nome as servico, u.nome as profissional, u.telefone as tel_profissional 
                              FROM agendamentos a 
                              INNER JOIN servicos s ON a.servico = s.id 
                              INNER JOIN usuarios u ON a.funcionario = u.id 
                              WHERE a.cliente = '$id_cliente' AND a.status != 'Cancelado' 
                              ORDER BY a.data DESC, a.hora DESC");
        $res = $query->fetchAll(PDO::FETCH_ASSOC);
        $total_reg = @count($res);

        if($total_reg > 0){
            for($i=0; $i < $total_reg; $i++){
                $id = $res[$i]['id'];
                $data = $res[$i]['data'];
                $hora = $res[$i]['hora'];
                $status = $res[$i]['status'];
                $servico = $res[$i]['servico'];
                $profissional = $res[$i]['profissional'];
                $tel_prof = $res[$i]['tel_profissional'];
                
                $dataF = implode('/', array_reverse(explode('-', $data)));
                $horaF = date("H:i", strtotime($hora));
                
                // Format phone for WhatsApp link
                $tel_prof_clean = preg_replace('/[^0-9]/', '', $tel_prof);
                
                // WhatsApp Message
                $msg_whatsapp = "Olá, meu nome é " . $_SESSION['nome_cliente'] . ". Gostaria de cancelar meu agendamento de $servico no dia $dataF às $horaF.";
                $link_whatsapp = "http://api.whatsapp.com/send?phone=55$tel_prof_clean&text=" . urlencode($msg_whatsapp);

                $classe_status = 'text-primary';
                if($status == 'Aguardando Aprovação'){
                    $classe_status = 'text-warning';
                } else if($status == 'Concluído' || $status == 'Finalizado'){
                     $classe_status = 'text-success';
                }
         ?>

        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                         <h5 class="card-title"><?php echo $servico ?></h5>
                         <span class="<?php echo $classe_status ?> font-weight-bold"><?php echo $status ?></span>
                    </div>
                    
                    <p class="card-text mb-1"><i class="fa fa-calendar"></i> <?php echo $dataF ?> às <?php echo $horaF ?></p>
                    <p class="card-text mb-1"><i class="fa fa-user"></i> Profissional: <strong><?php echo $profissional ?></strong></p>
                    
                    <hr>
                    
                    <a href="<?php echo $link_whatsapp ?>" target="_blank" class="btn btn-outline-danger btn-sm w-100">
                        <i class="fa fa-whatsapp"></i> Solicitar Cancelamento
                    </a>
                </div>
            </div>
        </div>

        <?php 
            }
        }else{
            echo '<p class="text-center w-100">Você não possui agendamentos ativos.</p>';
        }
        ?>
    </div>
    
    <div class="mt-4">
        <a href="../logout-cliente.php" class="btn btn-danger">Sair da Conta</a>
    </div>
    </div>
</div>

<?php require_once("../rodape.php") ?>
