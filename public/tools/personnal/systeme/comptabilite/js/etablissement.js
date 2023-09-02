$(document).ready(function(){

    $(document).on('click',".btn_banque_update",function(){
        var nomBanque = $(this).closest("tr").find(".elem_nom_banque").text() ;
        // var prix = $(this).closest("tr").find(".elem_prix").text() ;
        var idBanque = $(this).data("value") ;

        var element = `
            <div class="w-100 text-left">
                <label for="cmp_nom_banque" class="font-weight-bold">Nom Banque</label>
                <input type="text" name="cmp_nom_banque" id="cmp_nom_banque" oninput="this.value = this.value.toUpperCase();" value="`+nomBanque+`" class="form-control" placeholder=". . .">
            </div>
            `

        $.confirm({
            title: "Modification",
            content:element,
            type:"orange",
            theme:"modern",
            buttons:{
                btn1:{
                    text: 'Annuler',
                    action: function(){}
                },
                btn2:{
                    text: 'Valider',
                    btnClass: 'btn-orange',
                    keys: ['enter', 'shift'],
                    action: function(){
                        var realinstance = instance.loading()
                        $.ajax({
                            url: routes.compta_banque_update,
                            type:'post',
                            cache: false,
                            data: {
                                nomBanque: $("#cmp_nom_banque").val(),
                                idBanque: idBanque,
                            },
                            dataType: 'json',
                            success: function(json){
                                realinstance.close()
                                $.alert({
                                    title: 'Message',
                                    content: json.message,
                                    type: json.type,
                                    buttons: {
                                        OK: function(){
                                            if(json.type == "green")
                                            {
                                                location.reload()
                                            }
                                        }
                                    }
                                });
                            },
                            error: function(resp){
                                realinstance.close()
                                $.alert(JSON.stringify(resp)) ;
                            }
                        })
                    }
                }
            }
        })
    })

    $(document).on('click',".btn_banque_delete", function(){
        var self = $(this)
        $.confirm({
            title: "Suppression",
            content:"Vous êtes sûre ?",
            type:"red",
            theme:"modern",
            buttons:{
                btn1:{
                    text: 'Non',
                    action: function(){}
                },
                btn2:{
                    text: 'Oui',
                    btnClass: 'btn-red',
                    keys: ['enter', 'shift'],
                    action: function(){
                        var realinstance = instance.loading()
                        $.ajax({
                            url: routes.compta_banque_delete,
                            type:'post',
                            cache: false,
                            data:{idBanque:self.data("value")},
                            dataType: 'json',
                            success: function(json){
                                realinstance.close()
                                $.alert({
                                    title: 'Message',
                                    content: json.message,
                                    type: json.type,
                                    buttons: {
                                        OK: function(){
                                            if(json.type == "green")
                                            {
                                                location.reload()
                                            }
                                        }
                                    }
                                });
                            },
                            error: function(resp){
                                realinstance.close()
                                $.alert(JSON.stringify(resp)) ;
                            }
                        })
                    }
                }
            }
        })
        return false ;
    })


})