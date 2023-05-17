$(document).ready(function(){
    var instance = new Loading(files.loading)
    $(".chosen_select").chosen({
        no_results_text: "Aucun resultat trouvé : "
    });
    $("#formTypeTva").submit(function(event){
        event.preventDefault() ;
        var self = $(this)
        $.confirm({
            title: "Confirmation",
            content:"Etes-vous sûre ?",
            type:"blue",
            theme:"modern",
            buttons:{
                btn1:{
                    text: 'Non',
                    action: function(){}
                },
                btn2:{
                    text: 'Oui',
                    btnClass: 'btn-blue',
                    keys: ['enter', 'shift'],
                    action: function(){
                    var data = self.serialize();
                    var realinstance = instance.loading()
                    $.ajax({
                        url: routes.param_tva_save_type,
                        type:"post",
                        data:data,
                        dataType:"json",
                        success : function(json){
                            realinstance.close()
                            $.alert({
                                title: 'Message',
                                content: json.message,
                                type: json.type,
                                buttons: {
                                    OK: function(){
                                        if(json.type == "green")
                                        {
                                            $("input").val("")
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

    $("#input_type_tva").val("")

    $(".tva_check_all").click(function(){
        var self = $(this)
        $(this).closest('table').find('.type_tva_check').each(function(){
            if (!$(this).is(':checked'))
            {
                self.closest('table').find('.type_tva_check').prop('checked', true);
            }
        })
    })
    
    $(".tva_off_all").click(function(){
        var self = $(this)
        $(this).closest('table').find('.type_tva_check').each(function(){
            if ($(this).is(':checked'))
            {
                self.closest('table').find('.type_tva_check').prop('checked', false);
            }
        })
    })

    function updateTvaProduit(info)
    {
        var realinstance = instance.loading()
        $.ajax({
            url: routes.param_tva_update_produit,
            type:'post',
            cache: false,
            data: {info:info},
            dataType: 'json',
            success: function(json){
                realinstance.close() ;
                $.alert({
                    title: 'Message',
                    content: json.message,
                    type: json.type,
                });
            },
            error: function(resp){
                realinstance.close() ;
                $.alert(JSON.stringify(resp)) ;
            }
        })
        
    }

    $("#add_elem_type_tva").click(function(){
        if($("#input_type_tva").val() == "")
        {
            $.alert({
                title: "Type indéfini",
                content:"Veuillez séléctionner un type",
                type:"orange"
            })

            return false ;
        }
        var elems = [] ;
        $(this).closest('table').find('.type_tva_check').each(function(){
            var items = {} 
            if ($(this).is(':checked'))
            {
                var item = $(this).closest('tr') ; 
                $(this).closest('tr').remove() ;
                items = {
                    idP : item.find(".produit_enr_id").val(),
                    idType : $("#input_type_tva").val()
                }
                elems.push(items)
                $("#table_type_tva").find('tbody').append(item) ;
            }
        })
        updateTvaProduit(elems)
    })

    $("#remove_elem_type_tva").click(function(){
        var elems = [] ;
        $(this).closest('table').find('.type_tva_check').each(function(){
            var items = {} 
            if ($(this).is(':checked'))
            {
                var item = $(this).closest('tr') ; 
                $(this).closest('tr').remove() ;
                items = {
                    idP : item.find(".produit_enr_id").val(),
                    idType : ""
                }
                elems.push(items)
                $("#table_produits").find('tbody').append(item) ;
            }
            
        })
        updateTvaProduit(elems)
    })

    function displayTvaType(inputValue)
    {
        var data = new FormData() ;
        data.append("idTypeTva",inputValue)
        var realinstance = instance.loading()
        $.ajax({
            url: routes.param_display_elem_type_tva,
            type:'post',
            cache: false,
            data:data,
            dataType: 'html',
            processData: false, 
            contentType: false,
            success: function(resp){
                realinstance.close() ;
                var type_tvas = resp.split("#@@@#")[0]
                var produits = resp.split("#@@@#")[1]
              
                $("#table_produits").find('tbody').html(produits) ;
                $("#table_type_tva").find('tbody').html(type_tvas) ;
            },
            error: function(resp){
                realinstance.close() ;
                $.alert(JSON.stringify(resp)) ;
            }
        })
    }
    
    // var oldText = ''
    $(".btn_type_tva").click(function(){
        var target = $(this).data("target")
        var self = $(this)
        // if($(target).val() != "")
        // {
        //     $.confirm({
        //         title: "Sauvegarde "+oldText.split(" : ")[0].toUpperCase(),
        //         content:"Voulez-vous enregistrer les changements sur "+oldText.split(" : ")[0].toUpperCase(),
        //         type:"blue",
        //         theme:"modern",
        //         buttons:{
        //             btn1:{
        //                 text: 'Non',
        //                 action: function(){
        //                     var btnClass = self.data("class")
        //                     var currentbtnClass = "btn-outline-"+btnClass.split("-")[1]
        //                     var inputValue = self.attr("value")
        //                     oldText = self.text()
        //                     $(target).val(inputValue) ;
        //                     $(".label_type_tva").text(self.text())
        //                     displayTvaType(inputValue)
        //                 }
        //             },
        //             btn2:{
        //                 text: 'Oui',
        //                 btnClass: 'btn-blue',
        //                 keys: ['enter', 'shift'],
        //                 action: function(){
                            
        //                 }
        //             }
        //         }
        //     })
        // }
        var btnClass = self.data("class")
        var currentbtnClass = "btn-outline-"+btnClass.split("-")[1]
        var inputValue = self.attr("value")
        // oldText = self.text()
        $(target).val(inputValue) ;
        $(".label_type_tva").text(self.text())

        $(this).addClass(btnClass)
        $(this).removeClass(currentbtnClass)
        displayTvaType(inputValue)

        $(this).addClass(btnClass)

        $(this).removeClass(currentbtnClass)
        $(".btn_type_tva").each(function(){
            if (!self.is($(this))) {
                $(this).addClass(currentbtnClass) ; 
                $(this).removeClass(btnClass);
            }
        })

    })

    var elemSearch = [
        {
            name: "idC",
            action:"change",
            selector : ".tva_search_categorie"
        },
        {
            name: "produit",
            action:"input",
            selector : ".rch_produit"
        }
    ]
    function searchProduitInTypeTva(content,selector)
    {
        var instance = new Loading(files.search) ;
        $(content).find('tbody').html(instance.search(3)) ;
        var formData = new FormData() ;

        if(selector.is('select'))
        {
            formData.append("idC",selector.val());
            formData.append("produit",selector.closest('.row').find(".rch_produit").val());
        }
        else
        {
            formData.append("idC",selector.closest('.row').find(".tva_search_categorie").val());
            formData.append("produit",selector.val());
        }
            
        if(content == "#table_produits")
            formData.append("tvaType","-");
        else
            formData.append("tvaType",$("#input_type_tva").val());
        $.ajax({
            url: routes.param_search_prd_in_tva_type ,
            type: 'post',
            cache: false,
            data:formData,
            dataType: 'html',
            processData: false, // important pour éviter la transformation automatique des données en chaîne
            contentType: false, // important pour envoyer des données binaires (comme les fichiers)
            success: function(response){
                $(content).find('tbody').html(response) ;
            }
        })
    }

    elemSearch.forEach(elem => {
        $(document).on(elem.action,elem.selector,function(){
            if($("#input_type_tva").val() != "")
            {
                var content = $(this).data("target")
                searchProduitInTypeTva(content, $(this))
            }
            else
            {
                $.alert({
                    title: "Type indéfini",
                    content:"Veuillez séléctionner un type",
                    type:"orange"
                })
            }
        })
    })

    $(".rch_produit").change(function(){
        if($("#input_type_tva").val() != "")
        {
            var content = $(this).data("target")
            searchProduitInTypeTva(content, $(this))
        }
        else
        {
            $.alert({
                title: "Type indéfini",
                content:"Veuillez séléctionner un type",
                type:"orange"
            })
        }
    })


})