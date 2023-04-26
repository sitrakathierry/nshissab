class AppBase
{
    constructor()
    {
        
    }

    checkData(elements)
      {
        var message = ""
        var type = ""
        var allow = true ;
        for (let i = 0; i < elements.length; i++) {
            const element = elements[i];
            if($(element.selector).val() == "")
            {
                message = element.title+" vide"
                type = element.type
                allow = false
                break ;
            }
        }

        if(!allow)
        {
            $.alert({
                title: "Message",
                content: message,
                type:type
            })
        }

        return allow
      }

    searchElement(resultContent, url, tableSearch, nbColumn)
    {
        var instance = new Loading(files.search) ;
        $(resultContent).html(instance.search(nbColumn)) ;
        var formData = new FormData() ;
        for (let j = 0; j < tableSearch.length; j++) {
            const search = tableSearch[j];
            formData.append(search.name,$("#"+search.selector).val());
        }
        $.ajax({
            url: url ,
            type: 'post',
            cache: false,
            data:formData,
            dataType: 'html',
            processData: false, // important pour éviter la transformation automatique des données en chaîne
            contentType: false, // important pour envoyer des données binaires (comme les fichiers)
            success: function(response){
                $(resultContent).html(response) ;
            }
        })
    }
}