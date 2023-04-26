$(document).ready(function(){
    $(".menuCheck").remove() ;

    var normal = `
        <button type="button" class="btn btn-sm effacerTout btn-secondary text-white"><i class="fa fa-close"></i>&nbsp;Annuler</button>
        <button type="submit" class="btn btn-sm ml-3 enregistre_create btn-perso-one"><i class="fa fa-save"></i>&nbsp;Enregistrer</button>
        `
    var custom = `
        <button type="button" class="btn btn-sm effacerTout btn-secondary text-white"><i class="fa fa-close"></i>&nbsp;Annuler</button>
        <button type="button" class="btn btn-sm ml-3 modifie_create btn-primary"><i class="fa fa-edit"></i>&nbsp;Modifier</button>
        <button type="button" class="btn btn-sm ml-3 supprime_create btn-danger"><i class="fa fa-trash"></i>&nbsp;Supprimer</button>
        `

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

    function emptyCreateMenuForm()
   {
       $("#nom").val("")
       $("#menu_parent_id").val("")
       $("#icone").val("")
       $("#icone").keyup()
       $("#route").val("")
       $("#rang").val("")
   }

   function effacerTout()
   {
        $(".effacerTout").click(function(){
            emptyCreateMenuForm() ;
            $(".menuCheck").each(function(){
                checkMenu($(this),false)
            })
            $("#menu_parent_id").trigger("chosen:updated");
            $(".idMenu").val("") ;
            $(".type").val("enregistrer") ;
            $(".foot_action").html(normal)
        })
   }
   effacerTout()

   $(".modifie_create").click(function(){
        $(".type").val("modifier") ;
        $("#formCreation").submit() ;
   })

   function modifieCreate()
   {
        $(".modifie_create").click(function(){
            $(".type").val("modifier") ;
            $("#formCreation").submit() ;
        })
   }

    function supprimeCreate()
    {
        $(".supprime_create").click(function(){
            $(".type").val("supprimer") ;
            $("#formCreation").submit() ;
       })
    }
   
    $("#menu_parent_id").chosen({no_results_text: "Aucun resultat trouvé : "});
    $(".menu_item").click(function(){
        var self = $(this)
        $(".idMenu").val(self.attr("value")) ;
        $(".foot_action").html(custom)
        effacerTout()
        modifieCreate()
        supprimeCreate()
        self.addClass("active")
        var instance = loading()
        $.ajax({
            url: routes.disp_edit_menu,
            type: 'post',
            data: {value:self.attr("value")},
            dataType:'json',
            success: function(res)
            {
                instance.close()
                if(res.type == "green")
                {
                    var menu = res.liste
                    $("#menu_parent_id").val(menu.parent)
                    $("#menu_parent_id").trigger("chosen:updated");
                    $("#nom").val(menu.nom.toUpperCase())
                    var icone = (menu.icone).split("-")
                    icone.splice(0,1) 
                    icone2 = icone.join("-")
                    $("#icone").val(icone2)
                    $("#icone").keyup()
                    $("#route").val(menu.route)
                    $("#rang").val(menu.rang)
                }
                else
                {
                    $.alert({
                        title: false,
                        type:res.type,
                        content: res.message
                    }) ;
                }
            }
        })
    })

   $("#formCreation").submit(function(event){  
        event.preventDefault() 
        var data = $(this).serialize();
        $.confirm({
            title: 'Confirmation',
            content:"Voulez-vous vraiment "+$('.type').attr('value')+" ?",
            type:"blue",
            theme:"modern",
            buttons : {
                OUI : function(){
                    var instance = loading()
                    $.ajax({
                        url: routes.admin_validCreation,
                        type:"post",
                        data:data,
                        dataType:"json",
                        success : function(json){
                            instance.close()
                            $.alert({
                                title: 'Message',
                                content: json.message,
                                type: json.type,
                                buttons:{
                                    OK: function(){
                                        if(json.type == "green")
                                        {
                                            location.reload()
                                        }
                                    }
                                }
                            });
                            if(json.type == "green")
                            {
                                effacerTout()
                                $(".effacerTout").click()
                            }
                        }
                    })
                },
                NON : function(){
                    effacerTout()
                    $(".effacerTout").click()
                }
            }
        })
        return false ;
   }) 

   $(".restore_menu").click(function(){
        var self = $(this)
        $.confirm({
            title:"Confirmation",
            content: "Etes-vous sûre de vouloir restaurer cet élément ?",
            theme:"modern",
            type: "blue",
            buttons : {
                OUI : function()
                {
                    var instance = loading()
                    $.ajax({
                        url:routes.restore_menu_corbeille,
                        type:'post',
                        data:{idMenu:self.attr("value")},
                        dataType:'json',
                        success: function(json){
                            instance.close()
                            $.alert({
                                title: 'Message',
                                content: json.message,
                                type: json.type,
                                buttons:{
                                    OK: function(){
                                        if(json.type == "green")
                                        {
                                            location.reload()
                                        }
                                    }
                                }
                            });
                        }
            
                    })
                },
                NON : function(){
                    effacerTout()
                    $(".effacerTout").click()
                }
            }
        })
   }) 
})