<div class="comment-item">
   <div class="row no-margin">
      
      <div class="col-lg-1 col-xs-12 col-sm-2 no-margin">
         <div class="avatar">
            <img alt="avatar" width="64" height="64" class="img-rounded" src="{avatar}">
         </div>
      </div>

      <div class="col-xs-12 col-lg-11 col-sm-10 no-margin">
         
         <div class="comment-body">
            <div class="meta-info">
               <div class="inline">
                  <h3><span class="label label-{grade}0">{grade}</span></h3>
               </div>
               <div class="inline">
                  <a href="#" class="bold">{username}</a> {opinions_count}
               </div>
               <div class="inline">
                  
               </div>
               <div class="date inline pull-right">
                  <samp>{date}</samp>
               </div>
            </div>
            
            <dl>
               <dt class="text-success">Преимущества:</dt>
               <dd>{comment_pro}</dd>
               <dt class="text-danger">Недостатки:</dt>
               <dd>{comment_contra}</dd>
               <dt>Комментарий:</dt>
               <dd>{comment}</dd>
            </dl>
            
            <div class="row">
               <div class="col-xs-12 col-sm-2">
                  <dl>
                     <dd>
                        <div class="progress">
                           <div class="progress-bar progress-bar-success" style="width: {agree_ratio}%"></div>
                           <div class="progress-bar progress-bar-danger" style="width: {reject_ratio}%"></div>
                        </div>
                        <span class="text-muted"><i class="fa fa-thumbs-up"></i> <small>{agree_count}</small></span>
                        <span class="pull-right text-muted"><small>{reject_count}</small> <i class="fa fa-thumbs-down"></i></span>
                     </dd>
                  </dl>                  
               </div>
            </div>
                        
         </div>

      </div>

   </div>
</div>
