$(document).ready(function() {
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
});