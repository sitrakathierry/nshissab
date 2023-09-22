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

    $(".menu_item").each(function(){
      $(this).find(".menuCheck").removeClass("fa text-info")
      $(this).find(".menuCheck").addClass("far")
      if($(this).find(".menuPlus").hasClass("text-info"))
      {
        $(this).find(".menuPlus").click()
      }  
    })

    $(".oneSociety").click(function(){
        if($(this).hasClass("active"))
        { 
          var attributionRoute = routes.manager_agence
          if($(this).hasClass("isAgent"))
            attributionRoute = routes.param_user_get_menu_Agent
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
                url: attributionRoute,
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
                        content:"Veuiller ajouter des menus"
                      })
                    
                }
            })
        }
    })
})