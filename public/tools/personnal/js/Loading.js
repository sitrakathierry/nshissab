class Loading
{
    constructor(filename)
    {
        this.filename = filename
    }

    loading()
    {
        var loading = `
        <div class="text-center">
            <img src="`+this.filename+`" class="img img-fluid" alt="">
        </div>
        `
        var alertInstance = $.alert({
            backgroundDismiss: true,
            title:false,
            content:`
                <style>
                    .jconfirm .jconfirm-box
                    {
                        background-color: transparent !important;
                        box-shadow: none !important;
                    }
                </style>
            `+loading,
            closeIcon: false,
            buttons: false,
            });
        
        return alertInstance ; 
    }

    search(column)
    {
        var element = `
        <tr>
            <td colspan="`+column+`" class="text-center">
                <img src="`+files.search+`" class="img img-fluid load_search" alt="">
            </td>
        </tr>
        `
        return element ; 
    }

    otherSearch()
    {
        var element = `
        <div class="text-center">
            <img src="`+files.search+`" class="img img-fluid load_search" alt="">
        </div>
        `
        return element ; 
    }
}


