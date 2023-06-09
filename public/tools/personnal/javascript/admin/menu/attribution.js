$(document).ready(function(){

  function loading()
  {
      var loading = `
      <div class="text-center">
          <img src="`+files.loading+`" class="img img-fluid" alt="">
      </div>
      `
      alertInstance = $.alert({
          title:false,
          content:loading,
          closeIcon: false,
          buttons: false
          });
      
      return alertInstance ; 
  }

    function parcoursMenu(menu) {
        menu.forEach(item => {
          $(".menu_item").each(function(){
            if($(this).attr("value") == item.id)
            {
                $(this).find(".menuCheck").removeClass("far")
                $(this).find(".menuCheck").addClass("fa text-info")
                $(this).find(".menuPlus").click()
            }
          })
          if (item.submenu) {
            parcoursMenu(item.submenu); // appel récursif sur les sous-menus
          }
        });
      }



    $(".oneSociety").click(function(){
        if($(this).hasClass("active"))
        {
          $(".menu_item").each(function(){
              $(this).find(".menuCheck").removeClass("fa text-info")
              $(this).find(".menuCheck").addClass("far")
              if($(this).find(".menuPlus").hasClass("text-info"))
              {
                $(this).find(".menuPlus").click()
              }  
          })
            var instance = loading()
            $.ajax({
                url: routes.manager_agence,
                type:'post',
                data:{idAgence:$(this).attr("value")},
                dataType:'json',
                success: function(response){
                    instance.close()
                    if(response.menuManager.length > 0)
                      parcoursMenu(response.menuManager)
                    else
                      $.alert({
                        title:"Message",
                        type:"orange",
                        content:"Cet agence n'a aucun menu"
                        
                      })
                    
                }
            })
        }
    })
})