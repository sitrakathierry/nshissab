$(document).ready(function(){
    var instance = new Loading(files.loading)
    var appBase = new AppBase()
    $(document).on('change', "#fact_btp_enoncee", function(){
        var realinstance = instance.loading()
        var data = new FormData()
        data.append('id',$(this).val())
        $.ajax({
            url: routes.ftr_batiment_categorie_get_opt,
            type:'post',
            cache: false,
            data:data,
            dataType: 'html',
            processData: false,
            contentType: false,
            success: function(response){
                realinstance.close()
                $("#fact_btp_categorie").html(response)
                $(".chosen_select").trigger("chosen:updated");
            },
            error: function(resp){
                realinstance.close()
                $.alert(JSON.stringify(resp)) ;
            }
        })
    })

    $(document).on('change',"#fact_btp_designation",function(){
        if($(".btn_designation_plus").data("value") == "EXIST" || $(this).val() == "")
            return false ;

        var realinstance = instance.loading()
        var data = new FormData()
        data.append('id',$(this).val())
        $.ajax({
            url: routes.ftr_btm_element_prix_get_opt,
            type:'post',
            cache: false,
            data:data,
            dataType: 'html',
            processData: false,
            contentType: false,
            success: function(response){
                realinstance.close()
                var prixs = response.split('@##@')[0]
                var mesure = response.split('@##@')[1]
                $("#fact_btp_mesure").val(mesure)
                $("#fact_btp_prix").html(prixs)
                $(".chosen_select").trigger("chosen:updated");
            },
            error: function(resp){
                realinstance.close()
                $.alert(JSON.stringify(resp)) ;
            }
        })
    })

    function insertBatElementDesignation(designation)
    {
        var realinstance = instance.loading()
        $.ajax({
            url: routes.prest_batiment_element_save,
            type:'post',
            cache: false,
            data:{btp_elem_nom:designation},
            dataType: 'json',
            success: function(json){
                realinstance.close()
                sessionStorage.setItem('btpElemId', json.idD);
                displayElementInTable() ;
            },
            error: function(resp){
                realinstance.close()
                $.alert(JSON.stringify(resp)) ;
            }
        })
    }

    function displayElementInTable()
    {
        if($(".btn_designation_plus").data("value") == "EXIST")
        {
            var designation = sessionStorage.getItem('btpElemId'); ;
            var designationText = $("#fact_btp_designation").val() ;
        }
        else if($(".btn_designation_plus").data("value") == "NEW")
        {
            var designation = $("#fact_btp_designation").val()
            var designationText = $("#fact_btp_designation").find("option:selected").text();
        }

        // console.log(designation) ;

        var enonceeText = $("#fact_btp_enoncee").find("option:selected").text();
        var enonceId = $("#fact_btp_enoncee").val()
        var enonceItem = $(document).find("#enoncee"+enonceId)
        var surfaceText = $("#fact_btp_surface").find("option:selected").text() ;
        var surfaceId = $("#fact_btp_surface").val() ;
        var mesure = $("#fact_btp_mesure").val()
        var prix = $("#fact_btp_prix").val()
        var infoSup = $("#fact_btp_info_sup").val()
        var forfait = $("#fact_is_forfait").val() ;
        var labelQte = forfait == "OUI" ? $("#fact_btp_label_qte").val() : $("#fact_btp_qte").val() ;
        var prixText = $("#fact_btp_prix").find("option:selected").text() ;
        var labelPrix = forfait == "OUI" ? prix : prixText ;
        var prixMontant = prixText.split(' | ') ;
        var valPrix = forfait == "OUI" ? prix : prixMontant[0] ;
        var qte = $("#fact_btp_qte").val()
        var tva = $("#fact_btp_tva_val").val() == "" ? 0 : parseFloat($("#fact_btp_tva_val").val())
        var categorieId = $("#fact_btp_categorie").val()
        var categorieText = $("#fact_btp_categorie").find("option:selected").text();   
        var total = (parseFloat(valPrix) * parseInt(qte))
        var totalTvaLigne = ((parseFloat(valPrix) * parseFloat(tva)) / 100) * parseInt(qte)
        totalTvaLigne = parseFloat(totalTvaLigne.toFixed(2)) ;

        var itemElem = `
            <tr class="surface`+surfaceId+`">
                <td>
                `+designationText+`
                <input type="hidden" name="fact_enr_btp_enonce_id[]" value="`+enonceId+`">
                <input type="hidden" name="fact_enr_btp_categorie_id[]" value="`+categorieId+`">
                <input type="hidden" name="fact_enr_btp_info_sup[]" value="`+infoSup+`">
                <input type="hidden" name="fact_enr_btp_element_id[]" value="`+designation+`">
                <input type="hidden" name="fact_enr_btp_surface_id[]" value="`+surfaceId+`">
                <input type="hidden" name="fact_enr_btp_designation[]" value="`+(designationText+mesure)+`">
                </td>
                <td>`+mesure+`</td>
                <td>
                `+labelPrix+`
                <input type="hidden" name="fact_enr_btp_prix[]" value="`+valPrix+`">
                </td>
                <td>
                    `+labelQte+`
                    <input type="hidden" name="fact_enr_btp_quantite[]" value="`+qte+`">
                    <input type="hidden" name="fact_enr_btp_forfait[]" value="`+$("#fact_is_forfait").val()+`">
                </td>
                <td>
                    `+totalTvaLigne+`(`+tva+`%)
                    <input type="hidden" name="fact_enr_btp_tva[]" value="`+tva+`">
                    <input type="hidden" name="fact_btp_total_tva_ligne[]" id="fact_btp_total_tva_ligne" value="`+totalTvaLigne+`">
                </td>
                <td>`+ total +`</td>
                <td class="text-center">
                    <button type="button" categorie="`+categorieId+`" enonce="`+enonceId+`" class="btn btn-sm btn-outline-danger supprLigneCat font-smaller"><i class="fa fa-times"></i></button>
                </td>
            </tr>
            `
         

        if(enonceItem.text() == "")
        {
            var itemPrestBtp = `
            <div class="table-responsive mt-3">
                <h5 class="title_form text-black text-uppercase" id="enoncee`+enonceId+`">Enonceée : `+enonceeText+`</h5>
                <table class="table table-sm table-bordered table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th colspan="7" class="text-uppercase">CATEGORIE : `+categorieText+` </th>
                        </tr>
                    </thead>
                    <tbody id="categorie`+categorieId+`">
                        <tr class="thead-light">
                            <th colspan="7" class="text-uppercase">SURFACE DE TRAVAIL : `+surfaceText+` ; INFO SUPPLEMENTAIRE : `+infoSup+`</th>
                        </tr>
                        <tr>
                            <th>Désignation</th>
                            <th>Mésure</th>
                            <th>Prix Unitaire HT</th>
                            <th>Qte</th>
                            <th>Montant TVA</th>
                            <th>Montant Total</th>
                            <th></th>
                        </tr>
                        `+itemElem+`
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="5">Total Catégorie</th>
                            <th colspan="2" class="bg-secondary text-white">
                                <span id="totalCatText`+categorieId+`">`+total+`</span>
                                <input type="hidden" id="totalCat`+categorieId+`" value="`+total+`">
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            `
            $("#detailPrestBatiment").append(itemPrestBtp)
            $("#ftr_recap_btp").append(`
                    <tr>
                        <td>`+enonceeText+`</td>
                        <td>
                            <span id="totalEnonceText`+enonceId+`">`+total+`</span>
                            <input type="hidden" id="totalEnonce`+enonceId+`" value="`+total+`">
                        </td>
                    </tr>
                `) ;
        }
        else
        {
            var categorieItem = $("#categorie"+categorieId)

            if(categorieItem.text() == "")
            {
                var itemCategorie = `
                    <table class="table table-sm table-bordered table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th colspan="7" class="text-uppercase">CATEGORIE : `+categorieText+` </th>
                            </tr>
                        </thead>
                        <tbody id="categorie`+categorieId+`">
                            <tr class="thead-light">
                                <th colspan="7" class="text-uppercase">SURFACE DE TRAVAIL : `+surfaceText+` ; INFO SUPPLEMENTAIRE : `+infoSup+`</th>
                            </tr>
                            <tr>
                                <th>Désignation</th>
                                <th>Mésure</th>
                                <th>Prix Unitaire HT</th>
                                <th>Qte</th>
                                <th>Montant TVA</th>
                                <th>Montant Total</th>
                                <th></th>
                            </tr>
                            `+itemElem+`
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="5">Total Catégorie</th>
                                <th colspan="2" class="bg-secondary text-white">
                                    <span id="totalCatText`+categorieId+`">`+total+`</span>
                                    <input type="hidden" id="totalCat`+categorieId+`" value="`+total+`">
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                `
                enonceItem.parent().append(itemCategorie) ;
            }
            else
            {
                // console.log(categorieItem)
                // console.log(categorieItem.html())

                var lastSurfaceItem = categorieItem.find('.surface'+surfaceId).last()
                
                // console.log(lastSurfaceItem) ;

                if (lastSurfaceItem.length == 0 ) {
                    var itemSurface = `
                    <tr class="thead-light">
                        <th colspan="7" class="text-uppercase">SURFACE DE TRAVAIL : `+surfaceText+` ; INFO SUPPLEMENTAIRE : `+infoSup+`</th>
                    </tr>
                    <tr>
                        <th>Désignation</th>
                        <th>Mésure</th>
                        <th>Prix Unitaire HT</th>
                        <th>Qte</th>
                        <th>Montant TVA</th>
                        <th>Montant Total</th>
                        <th></th>
                    </tr>
                    `+itemElem+`
                    `
                    categorieItem.append(itemSurface) ;
                }
                else
                {
                    $(itemElem).insertAfter(lastSurfaceItem) ;
                }

                var totalCatVal = $("#totalCat"+categorieId).val()
                $("#totalCatText"+categorieId).text(parseFloat(totalCatVal) + total)
                $("#totalCat"+categorieId).val(parseFloat(totalCatVal) + total)
            }

            var totalEnonceeVal = $("#totalEnonce"+enonceId).val()
            $("#totalEnonceText"+enonceId).text(parseFloat(totalEnonceeVal) + total)
            $("#totalEnonce"+enonceId).val(parseFloat(totalEnonceeVal) + total)
        }
        var totalHt = 0 ;
        $("#ftr_recap_btp").find('tr').each(function(){
            var totalEnonce = $(this).find('input').val()
            totalHt += parseFloat(totalEnonce)
        })
        $("#fact_btp_total_ht").text(totalHt)

        var totalTvaVal = $("#fact_btp_total_tva").val()
        $("#fact_btp_total_tva").val(parseFloat(totalTvaVal) + totalTvaLigne)
        $("#fact_btp_total_tva_text").text(parseFloat(totalTvaVal) + totalTvaLigne)

        $("#fact_btp_total_ttc_text").text((parseFloat(totalTvaVal) + totalTvaLigne) + totalHt)
        $("#fact_btp_total_ttc").val((parseFloat(totalTvaVal) + totalTvaLigne) + totalHt)

        var lettreTotal = NumberToLetter((parseFloat(totalTvaVal) + totalTvaLigne) + totalHt)
        $("#fact_somme_lettre").text(lettreTotal) ;

        $("#fact_btp_designation").val("")
        $("#fact_btp_mesure").val("")
        $("#fact_btp_prix").val("")
        $("#fact_btp_qte").val("")
        $("#fact_btp_tva_val").val("")
        $(".chosen_select").trigger("chosen:updated") ;

        if($(".btn_std_forfait").hasClass("btn-info"))
        {
            $(".btn_std_forfait").click() ;
        }
    }

    $(document).on('click',".ajout_fact_btp_element",function(){

        var result = appBase.verificationElement([
            $("#fact_btp_enoncee").val(),
            $("#fact_btp_categorie").val(),
            $("#fact_btp_designation").val(),
            // $("#fact_btp_info_sup").val(),
            $("#fact_btp_prix").val(),
            $("#fact_btp_qte").val(),
        ],[
            "Enoncée",
            "Catégorie",
            "Désignation",
            // "Information Supplémentaire",
            "Prix",
            "Quantié",
        ]) ;

        if(!result["allow"])
        {
            $.alert({
                title: 'Message',
                content: result["message"],
                type: result["type"],
            });

            return result["allow"] ;
        }
        
        if($(".btn_designation_plus").data("value") == "EXIST")
            insertBatElementDesignation($("#fact_btp_designation").val()) ;
        else if($(".btn_designation_plus").data("value") == "NEW")
            displayElementInTable() ;
    })

    $(document).on('click',".supprLigneCat",function(){
        if(!$(this).attr("disabled"))
        {
            var categorieId = $(this).attr("categorie") ;
            var enonceId = $(this).attr("enonce") ;
            var total = parseFloat($(this).closest('tr').find('td:nth-child(6)').text())
            var totalTvaLigne = parseFloat($(this).closest('tr').find("#fact_btp_total_tva_ligne").val()) 

            var totalCatVal = $("#totalCat"+categorieId).val()
            $("#totalCatText"+categorieId).text(parseFloat(totalCatVal) - total) 
            $("#totalCat"+categorieId).val(parseFloat(totalCatVal) - total)

            var totalEnonceeVal = $("#totalEnonce"+enonceId).val()
            $("#totalEnonceText"+enonceId).text(parseFloat(totalEnonceeVal) - total)
            $("#totalEnonce"+enonceId).val(parseFloat(totalEnonceeVal) - total)

            var totalHt = 0 ;
            $("#ftr_recap_btp").find('tr').each(function(){
                var totalEnonce = $(this).find('input').val()
                totalHt += parseFloat(totalEnonce)
            })
            $("#fact_btp_total_ht").text(totalHt)

            var totalTvaVal = $("#fact_btp_total_tva").val()
            $("#fact_btp_total_tva").val(parseFloat(totalTvaVal) - totalTvaLigne)
            $("#fact_btp_total_tva_text").text(parseFloat(totalTvaVal) - totalTvaLigne)

            $("#fact_btp_total_ttc_text").text((parseFloat(totalTvaVal) - totalTvaLigne) + totalHt)
            $("#fact_btp_total_ttc").val((parseFloat(totalTvaVal) - totalTvaLigne) + totalHt)

            var lettreTotal = NumberToLetter((parseFloat(totalTvaVal) - totalTvaLigne) + totalHt)
            $("#fact_somme_lettre").text(lettreTotal) ;
        }
        $(this).prop("disabled", true);
        $(this).closest('tr').remove() ;
    })

    $(".fact_btn_miseajour_batiment").click(function(){
        $("#formModifFactureBatiment").submit()
    })

    $("#formModifFactureBatiment").submit(function(){
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
                        url: routes.fact_rajoute_element_activites,
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
        return false ;
    })

    $(document).on("click",".btn_designation_plus",function(){
        var self = $(this) ;
        var realinstance = instance.loading()
        var formData = new FormData() ;
        formData.append("type_designation",self.data("value")) ;
        $.ajax({
            url: routes.ftr_batiment_designation_get,
            type:'post',
            cache: false,
            data:formData,
            dataType: 'html',
            processData: false,
            contentType: false,
            success: function(response){
                realinstance.close()
                $(".pbat_content_designation").html(response)
                if(self.data("value") == "NEW")
                {
                    if($(".btn_btp_forfait").hasClass("btn-outline-info"))
                    {
                        $(".btn_btp_forfait").click()
                    }
                }
                else
                {
                    if($(".btn_btp_forfait").hasClass("btn-info"))
                    {
                        $(".btn_btp_forfait").click()
                    }
                }
            },
            error: function(resp){
                realinstance.close()
                $.alert(JSON.stringify(resp)) ;
            }
        })
    }) ; 
})