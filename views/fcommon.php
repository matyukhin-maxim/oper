
    </div>  <!-- container -->
    <div class="clearfix"></div>
            <div id="push"></div>
    </div>   
    
    <div id="footer" class="text-center navbar-default">
       
      <div class="container">
          <p>Отдел <abbr title="55-88, 51-30, 50-98">ОИТ</abbr>. 
              <!--Почта: <a href="mailto:matyukhin-mp@dvgk.rao-esv.ru">Матюхин М.П.</a></p>-->
        <a href="http://www.dvgk.rao-esv.ru/ru/">Нерюнгринская ГРЭС</a>. 2015г.
      </div>
    </div>
    
    <div class="modal fade" id="universal" tabindex="-1" role="dialog" 
         aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-dialog-center">
            <div class="modal-content">
            </div>
        </div>
    </div>

    
    <?php foreach ($this->js as $filename) {
        printf(PHP_EOL .'<script type="text/javascript" src="%s"></script>', "/public/js/$filename.js");
    }?>
    
    <!--[if lt IE 9]>
    <script src="/public/js/html5shiv.js"></script>
    <script src="/public/js/respond.js"></script>
    <![endif]-->

    </body>
</html>