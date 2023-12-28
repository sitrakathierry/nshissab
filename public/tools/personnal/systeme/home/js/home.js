$(document).ready(function() {
    var instance = new Loading(files.loading)
    // var data = [
    //     [0, 4],
    //     [1, 8],
    //     [2, 5],
    //     [3, 10],
    //     [4, 6]
    // ];

    // var options = {
    //     series: {
    //         lines: { show: true },
    //         points: { show: true }
    //     },
    //     grid: { hoverable: true, clickable: true },
    //     xaxis: { ticks: data }
    // };

    // $.plot("#chart", [data], options);
    // $(".line").peity("line")

    /** This code runs when everything has been loaded on the page */
    /* Inline sparklines take their values from the contents of the tag */
    // $('.inlinesparkline').sparkline(); 

    /* Sparklines can also take their values from the first argument 
    passed to the sparkline() function */
    // var myvalues = [10,8,5,7,4,4,1];
    // $('.dynamicsparkline').sparkline(myvalues,{
    //     type: 'line', 
    //     barColor: 'green',
    //     width: 500,
    //     height: 100,
    // });

    /* The second argument gives options such as chart type */
    // $('.dynamicbar').sparkline(myvalues, {type: 'bar', barColor: 'green'} );

    // /* Use 'html' instead of an array of values to pass options 
    // to a sparkline with data in the tag */
    // $('.inlinebar').sparkline('html', {type: 'bar', barColor: 'red'} ); 
    var elementProgress = [
        {
            selector: '.progress_client',
            bgColor: '#1488CC',
        },
        {
            selector: '.progress_stock',
            bgColor: '#ADD100',
        },
        {
            selector: '.progress_caisse',
            bgColor: '#FAA235',
        },
        {
            selector: '.progress_facture',
            bgColor: '#EF473A',
        },
        {
            selector: '.progress_bon',
            bgColor: '#AA076B',
        }
    ] ; 
    
    
    elementProgress.forEach(function(elem){
        $(elem.selector).each(function(){
            var self = $(this)
            $(this).rProgressbar({
                percentage: parseFloat(self.data("value")),
                fillBackgroundColor: elem.bgColor,
                backgroundColor: 'lightgrey',
                height: '10px',
                // width: '100%',
            });
        })
    })


    var clientDonut = [] ;
    $('.progress_client').each(function(){
        var self = $(this)
        clientDonut.push({
        value: self.data("value"),
        label: self.data("label"),
        })
    })

    var factureDonut = []
    $('.progress_facture').each(function(){
        var self = $(this)
        factureDonut.push({
        value: self.data("value"),
        label: self.data("label"),
        })
    })

    try 
    {
        Morris.Donut({
            element: 'donut_client',
            data: clientDonut,
            formatter: function (x) { return x + "%"}
          }).on('click', function(i, row){
            console.log(i, row);
        });
    
        Morris.Donut({
            element: 'donut_facture',
            data: factureDonut,
            formatter: function (x) { return x + "%"}
          }).on('click', function(i, row){
            console.log(i, row);
        });
    
        // Use Morris.Bar
        Morris.Bar({
            element: 'bar_article',
            data: [
                {x: 'Article(s)', y: 25, z: 5, a: 1},
                // {x: '2011 Q2', y: 2, z: null, a: 1},
                // {x: '2011 Q3', y: 0, z: 2, a: 4},
                // {x: '2011 Q4', y: 2, z: 4, a: 3}
            ],
            xkey: 'x',
            ykeys: ['y', 'z', 'a'],
            labels: ['En Cours', 'Expiré', 'Déduit']
            }).on('click', function(i, row){
            console.log(i, row);
        });
    
        // Use Morris.Area instead of Morris.Line
        Morris.Area({
            element: 'area_caisse',
            data: [
                {x: '2023-08-25', y: 3, z: 30000},
                {x: '2023-08-12', y: 6, z: 60000},
                {x: '2023-07-03', y: 2, z: 20000},
                // {x: '2011 Q2', y: null, z: 1},
                // {x: '2011 Q3', y: 2, z: 5},
                // {x: '2011 Q4', y: 8, z: 2},
                // {x: '2012 Q1', y: 4, z: 4}
            ],
            xkey: 'x',
            ykeys: ['y'],
            ykeys: ['y', 'z'],
            labels: ['Vente','Montant']
            }).on('click', function(i, row){
            console.log(i, row);
        });
    
        // Use Morris.Bar
        Morris.Bar({
            element: 'line_bon',
            data: [
                {x: 'Bon commande(s)', y: 25, z: 5, a: 8},
                {x: 'Bon Livraison(s)', y: 2, z: 5},
                // {x: '2011 Q3', y: 0, z: 2, a: 4},
                // {x: '2011 Q4', y: 2, z: 4, a: 3}
            ],
            xkey: 'x',
            ykeys: ['y', 'z', 'a'],
            labels: ['En Cours', 'Validée', 'Supprimée']
            }).on('click', function(i, row){
            console.log(i, row);
        });
    }
    catch (error) {
        console.log(error)
    }
    
    $(document).on("click",".btn_edit_dep",function(){
        var tabElem = [
            "#home_user_nom",
            "#home_user_email",
            "#home_user_resp",
        ] ;

        for (let i = 0; i < tabElem.length; i++) {
            const element = tabElem[i];
            $(element).removeClass("text-primary") ;
            $(element).addClass("text-success") ;
            $(element).removeAttr("readonly") ;
        }

        $(this).parent().html(`
        <button type="button" class="btn btn-sm ml-3 btn_annule_modif btn-purple font-weight-bold text-white"><i class="fa fa-times"></i>&nbsp;Annuler</button>
        <button type="submit" class="btn btn-sm ml-3 btn-warning font-weight-bold text-white"><i class="fa fa-edit"></i>&nbsp;Mettre à jour</button>
        `)
    }) ;

    $(document).on("click",".btn_annule_modif",function(){
        location.reload() ;
    })

    $(document).on("click",".btn_annule_mdp",function(){
        $(this).closest(".text-left").parent().html(`
        <button type="button" class="btn btn_mdp_form btn-info btn-sm"><i class="fa fa-database"></i>&nbsp;Modifier mot de passe</button>
        `) ;
    })

    $("#formUser").submit(function(){
        var self = $(this)
        $.confirm({
            title: "Modification",
            content:"Êtes-vous sûre ?",
            type:"orange",
            theme:"modern",
            buttons:{
                btn1:{
                    text: 'Non',
                    action: function(){}
                },
                btn2:{
                    text: 'Oui',
                    btnClass: 'btn-orange',
                    keys: ['enter', 'shift'],
                    action: function(){
                        var data = self.serialize() ;
                        var realinstance = instance.loading()
                        $.ajax({
                            url: routes.home_profil_utilisateur_update,
                            type:'post',
                            cache: false,
                            data:data,
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
    }) ;

    $(document).on("click",".btn_mdp_form",function(){
        var self = $(this)
        var realinstance = instance.loading()
        $.ajax({
            url: routes.home_user_mdp_form,
            type:'post',
            cache: false,
            dataType: 'html',
            processData: false,
            contentType: false,
            success: function(response){
                realinstance.close()
                self.parent().html(response) ;
                $(".btn_edit_dep").click() ;
            },
            error: function(resp){
                realinstance.close()
                $.alert(JSON.stringify(resp)) ;
            }
        })
    }) ;

    $(document).on("click",".disp_mdp",function(){
        var target = $(this).data("target") ;
        if($(target).attr("type") == "text")
        {
            $(target).attr("type","password") ;
            $(this).html('<i class="fa fa-eye-slash" ></i>')
        }
        else
        {
            $(target).attr("type","text") ;
            $(this).html('<i class="fa fa-eye" ></i>')
        }
    }) ;

});