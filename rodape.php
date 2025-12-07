<?php require_once("sistema/conexao.php") ?>
<!-- footer section -->
  <footer class="footer_section">
    <div class="container">
      <div class="footer_content ">
        <div class="row ">
          <div class="col-md-5 col-lg-5 footer-col">
            <div class="footer_detail">
              <a href="index.php">
                <h4>
                  <?php echo $nome_sistema ?>
                </h4>
              </a>
              <p>
                <?php echo $texto_rodape ?>
              </p>
            </div>
          </div>
          <div class="col-md-7 col-lg-4 ">
            <h4>
              Contatos
            </h4>
            <div class="contact_nav footer-col">
              <a href="">
                <i class="fa fa-map-marker" aria-hidden="true"></i>
                <span>
                  <?php echo $endereco_sistema ?>
                </span>
              </a>
              <a href="http://api.whatsapp.com/send?1=pt_BR&phone=<?php echo $tel_whatsapp ?>">
                <i class="fa fa-phone" aria-hidden="true"></i>
                <span>
                  Whatsapp : <?php echo $whatsapp_sistema ?>
                </span>
              </a>
              <a href="">
                <i class="fa fa-envelope" aria-hidden="true"></i>
                <span>
                  Email : <?php echo $email_sistema ?>
                </span>
              </a>
            </div>
          </div>
          <div class="col-lg-3">
            <div class="footer_form footer-col">
              <h4>
                CADASTRE-SE
              </h4>
              <form id="form_cadastro">
                <input type="text" name="telefone" id="telefone_rodape" placeholder="Seu Telefone DDD + número" />
                <input type="text" name="nome" placeholder="Seu Nome" />
                <button type="submit">
                  Cadastrar
                </button>
              </form>
              <br><small><div id="mensagem-rodape"></div></small>
            </div>
          </div>
        </div>
      </div>
    </div>
    
  </footer>
  <!-- footer section -->

  <a href="http://api.whatsapp.com/send?1=pt_BR&phone=<?php echo $tel_whatsapp ?>" class="whatsapp-float" target="_blank">
      <i class="fa fa-whatsapp"></i>
  </a>

  <!-- Floating Cart Button -->
  <a href="#" class="cart-float" data-toggle="modal" data-target="#modalCart" onclick="renderCart()">
      <i class="fa fa-shopping-cart"></i>
      <span id="cart-count">0</span>
  </a>

  <!-- Cart Modal -->
  <div class="modal fade" id="modalCart" tabindex="-1" role="dialog" aria-labelledby="cartModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="cartModalLabel">Seu Carrinho</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" id="cart-body">
           <!-- Cart Items Grouped by Professional will appear here -->
           <p class="text-center">Seu carrinho está vazio.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- jQery -->
  <script src="js/jquery-3.4.1.min.js"></script>
  <!-- popper js -->
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
  <!-- bootstrap js -->
  <script src="js/bootstrap.js"></script>
  <!-- owl slider -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
  <!-- custom js -->
  <script src="js/custom.js"></script>
  <!-- Cart Logic -->
  <script src="js/cart.js"></script>
  <!-- Google Map -->
  <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCh39n5U-4IoWpsVGUHWdqB6puEkhRLdmI&callback=myMap"></script>
  <!-- End Google Map -->

    <!-- Mascaras JS -->
<script type="text/javascript" src="sistema/painel/js/mascaras.js"></script>

<!-- Ajax para funcionar Mascaras JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.11/jquery.mask.min.js"></script> 


</body>

</html>


<script type="text/javascript">
  
$("#form_cadastro").submit(function () {

    event.preventDefault();
    var formData = new FormData(this);

    $.ajax({
        url: 'ajax/cadastrar.php',
        type: 'POST',
        data: formData,

        success: function (mensagem) {
            $('#mensagem-rodape').text('');
            $('#mensagem-rodape').removeClass()
            if (mensagem.trim() == "Salvo com Sucesso") {
               //$('#mensagem-rodape').addClass('text-success')
                $('#mensagem-rodape').text(mensagem)

            } else {

                //$('#mensagem-rodape').addClass('text-danger')
                $('#mensagem-rodape').text(mensagem)
            }


        },

        cache: false,
        contentType: false,
        processData: false,

    });

});


</script>