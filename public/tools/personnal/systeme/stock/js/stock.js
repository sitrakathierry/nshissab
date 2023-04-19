$(document).ready(function(){
    var produit_editor = new LineEditor(".produit_editor") ;

    $(".appr_ajout").click(function(){
        var self = $(this)
        $.confirm({
            boxWidth: '800px',
            useBootstrap: false,
            title:"Approvisionnement Type : <span class='text-warning'>"+self.attr('caption')+"</span>",
            content: `
            <div class="w-100 container-fluid">
                <div class="row">
                    <div class="col-md-4 text-left">
                        <label for="nom" class="font-weight-bold">Entrepôt</label>
                        <select name="type_societe" class="custom-select custom-select-sm" id="type_societe">
                            <option value="">Tous</option>
                        </select>
                    </div>
                    <div class="col-md-4 text-left">
                        <label for="nom" class="font-weight-bold">Produit</label>
                        <select name="type_societe" class="custom-select custom-select-sm" id="type_societe">
                            <option value="">Tous</option>
                        </select>
                    </div>
                    <div class="col-md-4 text-left">
                        <label for="nom" class="font-weight-bold">Prix Produit</label>
                        <select name="type_societe" class="custom-select custom-select-sm" id="type_societe">
                            <option value="">Tous</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 text-left">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="nom" class="mt-1 font-weight-bold">Indice</label>
                                <input type="text" name="nom" id="nom" class="form-control" placeholder=". . .">
                            </div>
                            <div class="col-md-6">
                                <label for="nom" class="mt-1 font-weight-bold">Quantité</label>
                                <input type="number" name="nom" id="nom" class="form-control" placeholder=". . .">
                            </div>
                        </div>

                        <label for="nom" class="mt-1 font-weight-bold">Fournisseurs</label>
                        <select name="type_societe" class="custom-select custom-select-sm" id="type_societe">
                            <option value="">Tous</option>
                        </select>

                        <label for="nom" class="mt-1 font-weight-bold">Expirée le</label>
                        <input type="text" name="nom" id="nom" class="form-control" placeholder=". . .">
                    </div>
                    <div class="col-md-6 text-left">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="nom" class="mt-1 font-weight-bold">Prix Achat</label>
                                <input type="number" name="nom" id="nom" class="form-control" placeholder=". . .">
                            </div>
                            <div class="col-md-6">
                                <label for="nom" class="mt-1 font-weight-bold">Charge</label>
                                <input type="number" name="nom" id="nom" class="form-control" placeholder=". . .">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <label for="nom" class="mt-1 font-weight-bold">Marge type</label>
                                <select name="type_societe" class="custom-select custom-select-sm" id="type_societe">
                                    <option value="">Tous</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="nom" class="mt-1 font-weight-bold">Marge valeur</label>
                                <input type="text" name="nom" id="nom" class="form-control" placeholder=". . .">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <label for="nom" class="mt-1 font-weight-bold">Prix de revient</label>
                                <input type="text" name="nom" id="nom" class="form-control" placeholder=". . .">
                            </div>
                            <div class="col-md-6">
                                <label for="nom" class="mt-1 font-weight-bold">Prix de vente</label>
                                <input type="text" name="nom" id="nom" class="form-control" placeholder=". . .">
                            </div>
                        </div>
                    </div>
                </div>
                <h6 class="font-weight-bold mt-1">Montant Total : <span class="text-warning">10000 KMF</span></h6>
            </div>
            `,
            theme:"modern",
            type:'blue',
            buttons:{
                btn1:{
                    text: 'Annuler',
                    action: function(){}
                },
                btn2:{
                    text: 'Ajouter',
                    btnClass: 'btn-blue',
                    keys: ['enter', 'shift'],
                    action: function(){
                        $.alert("Ajoutée !!! ")
                    }
                }
            }
        })
    })
})