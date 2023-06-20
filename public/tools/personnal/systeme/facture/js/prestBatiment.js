$(document).ready(function(){
    var instance = new Loading(files.loading)

    $(".chosen_select").chosen({
        no_results_text: "Aucun resultat trouvé : "
    });

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

    $(document).on('click',".ajout_fact_element",function(){
        var enonceeText = $("#fact_btp_enoncee").find("option:selected").text();
        var enonceId = $("#fact_btp_enoncee").val()

        var enonceItem = $(document).find("#enoncee"+enonceId)
        var designation = $("#fact_btp_designation").val()
        var designationText = $("#fact_btp_designation").find("option:selected").text();
        var mesure = $("#fact_btp_mesure").val()
        var prix = $("#fact_btp_prix").val()
        var prixText = $("#fact_btp_prix").find("option:selected").text() ;
        var prixMontant = prixText.split(' | ') ;
        var qte = $("#fact_mod_prod_qte").val()
        var tva = $("#fact_mod_prod_tva_val").val()
        var total = (parseFloat(prixMontant[0]) * parseInt(qte))

        var itemElem = `
            <tr>
                <td>`+designationText+`</td>
                <td>`+mesure+`</td>
                <td>`+prixText+`</td>
                <td>`+qte+`</td>
                <td>`+tva+`</td>
                <td>`+ total +`</td>
                <td>
                    <button type="button" class="btn btn-sm btn-outline-danger supprLigneCat font-smaller"><i class="fa fa-times"></i></button>
                </td>
            </tr>
            `
                
        if(enonceItem.text() == "")
        {
            var categorieId = $("#fact_btp_categorie").val()
            var categorieText = $("#fact_btp_categorie").find("option:selected").text();
            
            var itemPrestBtp = `
            <div class="table-responsive mt-3">
                <h5 class="title_form text-black text-uppercase" id="enoncee`+enonceId+`">Enonceée : `+enonceeText+`</h5>
                <table class="table table-sm table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th colspan="7" class="text-uppercase" id="categorie`+categorieId+`">CATEGORIE : `+categorieText+` </th>
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
                    </thead>
                    <tbody>
                        `+itemElem+`
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="5">Total Catégorie</th>
                            <th colspan="2" class="bg-secondary text-white">-</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            `
            $("#detailPrestBatiment").append(itemPrestBtp)
        }
        else
        {
            var categorieId = $("#fact_btp_categorie").val()
            var categorieItem = $(document).find("#categorie"+categorieId)
            var categorieText = $("#fact_btp_categorie").find("option:selected").text();

            if(categorieItem.text() == "")
            {
                var itemCategorie = `
                <table class="table table-sm table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th colspan="7" class="text-uppercase" id="categorie`+categorieId+`">CATEGORIE : `+categorieText+` </th>
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
                    </thead>
                    <tbody>
                        `+itemElem+`
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="5">Total Catégorie</th>
                            <th colspan="2" class="bg-secondary text-white">-</th>
                        </tr>
                    </tfoot>
                </table>
                `
                enonceItem.parent().append(itemCategorie)
            }
            else
            {
                categorieItem.closest('table').find('tbody').append(itemElem)
            }
        }

    })

    $(document).on('click',".supprLigneCat",function(){
        $(this).closest('tr').remove()
    })
})