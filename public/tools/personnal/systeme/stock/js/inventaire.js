$(document).ready(function(){
    var instance = new Loading(files.loading)
    var elem_search_invent = [
        {
            name: "idE",
            selector : "stock_entrepot"
        },
        {
            name: "idC",
            selector : "stock_categorie"
        },
        {
            name: "idP",
            selector : "stock_produit"
        }
    ]

    function searchStockEntrepot()
    {
        var myinstance = new Loading(files.search) ;
        $(".elem_inventaire").html(myinstance.search(9)) ;
        var formData = new FormData() ;
        for (let j = 0; j < elem_search_invent.length; j++) {
            const search = elem_search_invent[j];
            formData.append(search.name,$("#"+search.selector).val());
        }
        $.ajax({
            url: routes.stock_search_stock_entrepot ,
            type: 'post',
            cache: false,
            data:formData,
            dataType: 'html',
            processData: false, // important pour éviter la transformation automatique des données en chaîne
            contentType: false, // important pour envoyer des données binaires (comme les fichiers)
            success: function(response){
                $(".elem_inventaire").html(response) ;
            },
            error: function(resp){
                $.alert(JSON.stringify(resp)) ;
            }
        })
    }

    elem_search_invent.forEach(elem => {
        $("#"+elem.selector).change(function()
        {
            searchStockEntrepot()
        })
        // console.log(elem.selector)
    })

    $(document).on("click",".check_export",function(){
        if($(this).hasClass("btn-outline-success"))
        {
            $(this).removeClass("btn-outline-success")
            $(this).addClass("btn-success")
            $(this).html($(this).data('check') + '<input type="hidden" name="stock_id_histo_ent[]" class="stock_id_histo_ent" value="'+$(this).data('value')+'">')
        }
        else
        {
            $(this).removeClass("btn-success")
            $(this).addClass("btn-outline-success")
            $(this).html($(this).data('init'))
        }
    })

    $(".export_inventaire").click(function(){
        var lenData = $(".stock_id_histo_ent").length ;
        if(lenData <= 0)
        {
            $.confirm({
                title: "Seléction",
                content:"Aucun élément n'est selétionné. Voulez-vous tout seléctionner ? ",
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
                            $(".check_export").each(function(){
                                $(this).click()
                            })
                            $("#formInventaire").submit()
                        }
                    }
                }
            })
            return false ;
        }
        $("#formInventaire").submit()
    })

})