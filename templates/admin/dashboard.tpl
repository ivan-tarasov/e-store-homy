<section class="container admin-page" style="padding:2em 0;">
   <div class="row">
      <div class="col-md-3">{nav}</div>
      <div class="col-md-9">
         <h1>Панель администратора</h1>
         <div class="admin-cards">{cards}</div>

         <h2 style="margin-top:2em;">Последние заказы</h2>
         <table class="table">
            <thead>
               <tr>
                  <th>Номер</th>
                  <th>Дата</th>
                  <th>Клиент</th>
                  <th class="text-end">Сумма</th>
                  <th>Позиций</th>
               </tr>
            </thead>
            <tbody>{rows}</tbody>
         </table>
         <p><a href="/admin/orders/">Все заказы →</a></p>
      </div>
   </div>
</section>
