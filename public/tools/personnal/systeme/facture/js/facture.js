$(document).ready(function(){  
    var facture_editor = new LineEditor(".facture_editor") ;
    var instance = new Loading(files.loading)
    $("#fact_date").datepicker() ;
    $("#agd_ech_date").datepicker() ;
    $("#agd_acp_date").datepicker() ;
    
    $("#formFacture").submit(function(event){
        event.preventDefault()
        var self = $(this)
        $(".facture_editor").val(facture_editor.getEditorText('.facture_editor'))
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
                        url: routes.fact_save_activites,
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
                                            $(".chosen_select").val("")
                                            $("#fact_remise_prod_general").val("")
                                            $("#fact_prod_tva").val("")
                                            $(".chosen_select").trigger("chosen:updated");
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
        
    $("#fact_client").change(function(){
            var selectedText = $(this).find("option:selected").text();
            $(".fact_table_client").text(selectedText)
    })

    $(".fact_btn_type").click(function(){
        var btnClass = $(this).data("class")
        var target = $(this).data("target")
        var currentbtnClass = "btn-outline-"+btnClass.split("-")[1]
        var inputValue = $(this).attr("value")
        var self = $(this)
        var btnText = $(this).data("text")
        $(target).val(inputValue) ;

        var paiementArray = ["ES","VR","CH","CB","MN","CR","AC"]

        var modePaiement = $(this).data('mode').split(",")
        
        paiementArray.forEach(elem => {
            $(".fact_btn_paiement").filter("." + elem).prop("disabled", true);
            $(".fact_btn_paiement").filter("." + elem).addClass("btn-outline-info") ; 
            $(".fact_btn_paiement").filter("." + elem).removeClass("btn-info");
        })
        $("#fact_paiement").val("")
        $(".fact_table_paiement").text("")
        modePaiement.forEach(elem => {
            $(".fact_btn_paiement" + "." + elem).removeAttr("disabled");
        })

        var reference = $(this).data("reference")

        if(reference != "DF")
        {
            $("#fact_libelle").hide();
            $(".fact_libelle_caption").text("")

            $("#fact_num").hide();
            $(".fact_num_caption").text("")
        }

        $(this).addClass(btnClass)
        $(this).removeClass(currentbtnClass)
        $(".fact_table_type").text(btnText)
        $(".fact_btn_type").each(function(){
            if (!self.is($(this))) {
                $(this).addClass(currentbtnClass) ; 
                $(this).removeClass(btnClass);
            }
        })

        $(".agd_acompte").hide()
    })

    $(".fact_btn_paiement").click(function(){
        var btnClass = $(this).data("class")
        var target = $(this).data("target")
        var currentbtnClass = "btn-outline-"+btnClass.split("-")[1]
        var inputValue = $(this).attr("value")
        var btnText = $(this).data("text")
        var self = $(this)
        var numCaption = $(this).data('numcaption')
        var libelleCaption = $(this).data('libelle')

        $(target).val(inputValue) ;

        $(".fact_table_paiement").text(btnText)

        if(libelleCaption != "")
        {
            $("#fact_libelle").show();
            $(".fact_libelle_caption").text(libelleCaption)
        }
        else
        {
            $("#fact_libelle").hide();
            $(".fact_libelle_caption").text("")
        }   

        if(numCaption != "")
        {
            $("#fact_num").show();
            $(".fact_num_caption").text(numCaption)
        }
        else
        {
            $("#fact_num").hide();
            $(".fact_num_caption").text("")
        }

        $(this).addClass(btnClass)
        $(this).removeClass(currentbtnClass)
        $(".fact_btn_paiement").each(function(){
            if (!self.is($(this))) {
                $(this).addClass(currentbtnClass) ; 
                $(this).removeClass(btnClass);
            }
        })

        // AGENDA

        if ($(this).hasClass('CR')) {
            $("#agd_echance").show()
        }
        else
        {
            $("#agd_echance").hide()
        }

        // Agenda acompte

        if ($(this).hasClass('AC')) {
            $(".agd_acompte").show()
        }
        else
        {
            $(".agd_acompte").hide()
        }

        if($(this).hasClass('AC') || $(this).hasClass('CR'))
        {
            var contentMontant = $(".contentMontant").html()
            if(contentMontant != "")
            {
                $(".teleportMontant").html(contentMontant)
                $(".contentMontant").empty()
            }
        }else{
            var teleportMontant = $(".teleportMontant").html()
            if(teleportMontant != "")
            {
                $(".contentMontant").html(teleportMontant)
                $(".teleportMontant").empty()
            }
        }
    })

    $(".fact_btn_modele").click(function(){
        var btnClass = $(this).data("class")
        var target = $(this).data("target")
        var currentbtnClass = "btn-outline-"+btnClass.split("-")[1]
        var inputValue = $(this).attr("value")
        var self = $(this)
        var btnText = $(this).data("text")

        $(target).val(inputValue) ;

        $(this).addClass(btnClass)
        $(this).removeClass(currentbtnClass)

        $(".fact_title_modele").text(btnText)
        // $(".fact_caption_total_general").text("TOTAL "+btnText)

        $(".fact_btn_modele").each(function(){
            if (!self.is($(this))) {
                $(this).addClass(currentbtnClass) ; 
                $(this).removeClass(btnClass);
            }
        })

    })

})