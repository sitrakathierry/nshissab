class LineEditor
   {
      constructor(element)
      {
         this.element = element ;
         $(this.element).Editor();
         this.idElem = $(this.element).attr("id")
         this.menuBar = $("#menuBarDiv_"+this.idElem)
      }

      setEditorText(content)
      {
         this.menuBar.parent().find(".Editor-editor").html(content)
      }

      getEditorText()
      {
         var response = this.menuBar.parent().find(".Editor-editor").html();
         return response ;
      }
   }