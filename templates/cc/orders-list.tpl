<div class="row">
   <div class="col-sm-12">

      <div class="panel panel-default">

         <div class="panel-heading">
            <!--span class="pull-right lead">
               {status}
            </span>
            <div class="panel-title">
               <code><abbr title="{was_created}" class="lead">{id}</abbr></code>
               <i class="fa fa-clock-o"></i> {was_created}
            </div-->

            <div class="row">

               <div class="col-sm-12">
                  <div class="col-lg-1 col-sm-1 hidden-xs">
                     <i class="fa fa-4x fa-user"></i>
                  </div>
                  <div class="col-lg-8 col-sm-8 lead">
                     {order_fio}<br />
                     <code>{order_phone}</code>
                     <!--dd class="small"><i class="fa fa-map-marker"></i> {user_city}</dd>
                     <small><i class="fa fa-clock-o"></i> {date_post}</small-->
                  </div>
                  <div class="col-lg-3 col-sm-3 lead">
                     <h2><code class="pull-right">{id}</code></h2>
                  </div>
                  <!--div class="col-lg-4 col-sm-12">
                     {user_info}
                  </div>
                  <div class="col-lg-4 col-sm-12">
                     {note_user}
                  </div-->

               </div>

            </div>

            <div class="col-sm-12">
               <div class="progress{order_bar_striped}">
                  <div class="progress-bar progress-bar-{order_status_style}" style="width: {order_status_stage}%">
                     <span>{order_status_name}</span>
                  </div>
               </div>
					<!--div class="stamp-success">
						<img src="/img/cc/not-payd.png" height="150" />
					</div-->
            </div>

         </div>

         <div class="row">
            <div class="col-md-12">

               <ul class="nav nav-tabs" role="tablist">
                  <li role="presentation" class="active">
                     <a href="#info-{id}" aria-controls="#info-{id}" role="tab" data-toggle="tab">
                        <i class="fa fa-lg fa-list-ol" aria-hidden="true"></i>
                        Список позиций в заказе
                     </a>
                  </li>
                  <li role="presentation">
                     <a href="#timeline-{id}" aria-controls="timeline-{id}" role="tab" data-toggle="tab">
                        <i class="fa fa-lg fa-road" aria-hidden="true"></i>
                        Подробное движение заказа
                     </a>
                  </li>
               </ul>

               <div class="tab-content">

                  <!-- СПИСОК ПОЗИЦИЙ -->
                  <div role="tabpanel" class="tab-pane active" id="info-{id}">
                     <div class="col-sm-12">
                        <table class="table table-striped">
                           <thead>
                              <tr>
                                 <th>#</th>
                                 <th>Группа товара</th>
                                 <th>Товарная позиция</th>
                                 <th>Количество</th>
                                 <th>Цена</th>
                                 <th>Итого</th>
                              </tr>
                           </thead>
                           <tbody>
                              {order_cart}
                           </tbody>
                        </table>
                        <h3 class="pull-right">{order_total}</h3>
                     </div>
                     <div class="col-sm-12">
                        <p>{note_user}</p>
								<div class="space60"></div>
                     </div>
                  </div>

                  <!-- TIMELINE -->
                  <div role="tabpanel" class="tab-pane" id="timeline-{id}">
                     <ul class="timeline">
                        {timeline}
                     </ul>
                  </div>

               </div>

            </div>
         </div>

         <div class="panel-footer">
            <div class="row">
               <div class="col-sm-12">

                  <div class="btn-group" role="group">

                     <button type="button" class="btn btn-danger" id="order-delete-{id}" data-options='{"order_id":"{id}"}'>
                        <i class="fa fa-lg fa-trash-o"></i>
                     </button>

                  </div>

                  <div class="pull-right">
                     <div class="btn-group" role="group">
                        {btn-status}
                     </div>
                  </div>

						<div class="paid-status {paid_status}"></div>

               </div>
            </div>
         </div>

      </div>

   </div>
</div>
