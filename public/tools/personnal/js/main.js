$(document).ready(function(){
    // bloquer la cache 
    $.ajaxSetup({
        cache: false
      });
      
    // fonction  générer un menu de type accordion
    function generateAccordionMenu(menuData, parentElement) 
      {
          var menuList = $('<ul>').addClass('accordion-menu list-unstyled components');
          $.each(menuData, function(index, item) {
            var menuItem = $('<li>');
            var menuItemLink = $('<a>').attr('href',routes[item.route]).html('<i class="fa ' + item.icone + '"></i>&nbsp;' + item.nom);
            
          if (item.submenu && item.submenu.length > 0) {
                  menuItemLink.addClass('accordion-toggle collapsed');
                  menuItemLink.attr('data-toggle', 'collapse');
                  menuItemLink.attr('data-parent','#submenu-' + item.id);
                  menuItemLink.attr('data-target', '#submenu-' + item.id);
                  
                  var submenu = $('<ul>').addClass('sub-menu list-unstyled collapse').attr('id', 'submenu-' + item.id);
                  generateAccordionMenu(item.submenu, submenu);
                  menuItem.append(menuItemLink).append(submenu);
              } else {
                  menuItem.append(menuItemLink);
              }
              menuList.append(menuItem);
          });
          
          parentElement.append(menuList);
    } 
    
    // appliquer la génération du menu de type accordion
    $.getJSON(routes.jsonPath, function(json) {
          generateAccordionMenu(json,$('.menu_accr'))
    });

    // indication de chargement
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

    // verification de la qualité du mot passe
    function checkPasswordStrength(password) {
        var passwordStrength = 0;
        var passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
        if (passwordRegex.test(password)) {
            passwordStrength = 4;
        } else {
            if (password.length >= 8) {
                passwordStrength++;
            }
            if (password.match(/[a-z]/)) {
                passwordStrength++;
            }
            if (password.match(/[A-Z]/)) {
                passwordStrength++;
            }
            if (password.match(/\d/)) {
                passwordStrength++;
            }
            if (password.match(/[@$!%*?&]/)) {
                passwordStrength++;
            }
        }
        return passwordStrength;
    }

    $("#password").keyup(function(){
        var passStrenght = checkPasswordStrength($(this).val())
        if(($(this).val()).length >= 8)
        {
            if(passStrenght <= 1)
            {
                $(".badge_pass").removeClass("badge-success")
                $(".badge_pass").removeClass("badge-warning")
                $(".badge_pass").addClass("badge-danger")
                $(".badge_pass").text("Faible")
            }
            else if(passStrenght == 2)
            {
                $(".badge_pass").removeClass("badge-success")
                $(".badge_pass").removeClass("badge-danger")
                $(".badge_pass").addClass("badge-warning text-white")
                $(".badge_pass").text("Moyen")
            }
            else
            {
                $(".badge_pass").removeClass("badge-warning")
                $(".badge_pass").removeClass("badge-danger")
                $(".badge_pass").addClass("badge-success")
                $(".badge_pass").text("Forte")
            }
        }
        else
        {
            $(".badge_pass").removeClass("badge-warning")
            $(".badge_pass").removeClass("badge-danger")
            $(".badge_pass").removeClass("badge-success")
            $(".badge_pass").addClass("badge-secondary")
            $(".badge_pass").text("Trop court")
        }  
    })
    
    function cleanFormSociete(data)
    {
        var dataForm = []
        var elements = data.split("&")
        for (let index = 0; index < elements.length; index++) {
            const element = elements[index];
            dataForm.push(element.split("=")[0]) ;
        }
        // const jsonData = JSON.stringify(dataForm);
        dataForm.forEach(elem => {
            $("#"+elem).val("")
        })
        $.ajax({
            url : routes.getRandomPass ,
            type:'post',
            data:{},
            dataType: 'json',
            success : function(resp){
                $("#password").val(resp.randomPass)
            }
        })
    }

    $("#formSociete").submit(function(event){
        event.preventDefault()
        var data = $(this).serialize();
        $.confirm({
            title: 'Confirmation',
            content:"Voulez-vous vraiment enregistrer ?",
            type:"blue",
            theme:"modern",
            buttons : {
                OUI : function(){
                    var instance = loading()
                    $.ajax({
                        url: routes.admin_saveSociete,
                        type:"post",
                        data:data,
                        dataType:"json",
                        success : function(json){
                            instance.close()
                            $.alert({
                                title: 'Message',
                                content: json.message,
                                type: json.type,
                            });
                            if(json.type == "green")
                            {
                                cleanFormSociete(data)
                            }
                        }
                    })
                },
                NON : function(){
                    cleanFormSociete(data)
                }
            }
        })
    })

    $(".menuPlus").click(function(){
        $(this).toggleClass("text-info")
        if($(this).hasClass("fa-plus"))
        {
            $(this).removeClass("fa-plus")
            $(this).addClass("fa-minus")
        }
        else
        {
            $(this).removeClass("fa-minus")
            $(this).addClass("fa-plus")
        }
        $(this).toggleClass("active")
    })

    function checkMenu(self,is_active)
    {
        if(is_active)
        {
            self.removeClass("far")
            self.addClass("fa text-info")
        }
        else
        {
            self.removeClass("fa text-info")
            self.addClass("far")
        }
    }

    $(".menuCheck").click(function(){
        var menuPlus = $(this).closest("li").find(".menuPlus")
        // console.log(menuPlus) ;
        if(menuPlus.length > 0)
        {
            if($(this).hasClass("fa text-info"))
            {
                checkMenu($(this),false)     
                if(menuPlus.hasClass("active"))
                {
                    menuPlus.click()
                }
                var child = menuPlus.data("target")
                $(child).find(".menuCheck").each(function(){
                    checkMenu($(this),false)
                })
            }
            else
            {
                checkMenu($(this),true)
                if(!menuPlus.hasClass("active"))
                {
                    menuPlus.click()
                }  
                var child = menuPlus.data("target")
                $(child).find(".menuCheck").each(function(){
                    checkMenu($(this),true)
                })
            }
        }
        else
        {
            var collapse = $(this).closest("ul").attr("id")
            var parent = $(this).closest("#accordion").find("i[data-target='#"+collapse+"']").closest("li").find(".menuCheck")

            if(!parent.hasClass("fa text-info"))
            {
                checkMenu(parent,true)
            }
            if($(this).hasClass("fa text-info"))
            {
                checkMenu($(this),false)
            }
            else
            {
                checkMenu($(this),true)
            }
        }   
    })

    function checkSociety(agence)
    {
        $.alert({
            title: false,
            content: agence,
            buttons : false,
            closeIcon: true
        })
    }

    $(".oneSociety").click(function(){
        var self = $(this)
        $(".oneSociety").each(function(){
            if($(this).hasClass("active"))
            {
                $(this).removeClass("active")
            }
        }) 

        self.toggleClass("active")
        // checkSociety(self.attr("value"))
    })

    function uncheckedSociety()
    {
        $(".oneSociety").each(function(){
            if($(this).hasClass("active"))
            {
                $(this).removeClass("active")
            }
        }) 
    }

    $(".effacerTout").click(function(){
        uncheckedSociety()
        $(".menuCheck").each(function(){
            checkMenu($(this),false)
        })
    })

    $(".enregistre_attr_menu").click(function(){
        $.confirm({
            title: 'Confirmation',
            content:"Voulez-vous vraiment enregistrer ?",
            type:"blue",
            theme:"modern",
            buttons : {
                OUI: function(){
                    var idS = $(".oneSociety.active").attr("value")
                    var menus = []
                    $(".menuCheck.text-info").each(function(){
                        menus.push($(this).attr("value"))
                    })
                    var instance = loading()
                    $.ajax({
                        url: routes.admin_save_attribution,
                        type: 'post',
                        data:{agence:idS,menus:menus},
                        dataType:'json',
                        success:function(res){
                            instance.close()
                            $.alert({
                                title: false,
                                type:res.type,
                                content:res.message
                            })
                            if(res.type == "green")
                            {
                                uncheckedSociety()
                                $(".menuCheck").each(function(){
                                    checkMenu($(this),false)
                                })
                            }
                        }
                    })
                },
                NON: function(){
                    uncheckedSociety()
                    $(".menuCheck").each(function(){
                        checkMenu($(this),false)
                    })
                }
            }
        }) 
    })

    $("#icone").keyup(function(){
        $(".icone_menu i").removeClass()
        $(".icone_menu i").addClass("fa fa-"+$(this).val())
    })
});
