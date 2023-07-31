<h2 class="border h1">Смена пароля</h2>
<section class="section">
   <p>Для смены пароля Вам необходимо ввести свой действующий пароль от учетной записи. Это необходимо для предотвращения несанкционированного доступа в Вашу учетную запись.</p>
   <p id="changepass-response"></p>
   
   <form id="chenge-password" role="form" class="form-horizontal cf-style-1 inner-top-xs">
   
      <div class="form-group">
         <label for="oldpass" class="col-sm-3 control-label">Текущий пароль</label>
         <div class="col-xs-6">
            <input type="password" id="oldpass" name="oldpass" class="le-input">
         </div>
      </div>

      <div class="form-group">
         <label for="new_pass" class="col-sm-3 control-label">Новый пароль</label>
         <div class="col-xs-6">
            <input type="password" id="newpass" name="newpass" class="le-input">
         </div>
      </div>
      
      <div class="form-group">
         <label for="new_pass_check" class="col-sm-3 control-label">Повтор нового пароля</label>
         <div class="col-xs-6">
            <input type="password" id="passchk" name="passchk" class="le-input">
         </div>
      </div>
      
      <div class="buttons-holder col-md-offset-3">
         <button type="submit" id="changepass-submit" class="le-button">Сменить пароль</button>
      </div>
         
   </form>

</section>
