// $(window).on('load', function() {
//     $('.flexslider').flexslider({
//         directionNav: true,
//         controlNav: true
//     });
// });

/**************************************************************
*                        FAQ                                   *
*                                                              *
***************************************************************/

var question = document.getElementsByClassName('question');
var i;
var icone;

for(i = 0; i < question.length ; i++){
    question[i].addEventListener('click', function(){
        icone = this.firstElementChild.firstElementChild;
        icone.classList.contains('fa-plus') ? icone.classList.replace('fa-plus', 'fa-minus') : icone.classList.replace('fa-minus','fa-plus');
        this.nextElementSibling.classList.toggle('hidden');

    })
}



/**************************************************************
*                        menu all products                     *
*                                                              *
***************************************************************/


$(window).on('load', function() {
    var choix = document.querySelector('#choix');
    if(choix){
        for(var i = 0; i < choix.children.length;i++){
            var li = choix.children;
            li[i].addEventListener("click" , function(){
                var value = this.attributes[0].value;
                var div = document.querySelector('#allProducts');
                var articles = document.querySelectorAll('div>article')
                for(var j = 0; j< div.children.length; j++){
                    if(articles[j].attributes[0].value !== value){
                        articles[j].classList.add('none');
                    }else if(articles[j].attributes[0].value === value){
                        articles[j].classList.remove('none');
                    }
        
                    if(value === "all active"){
                        articles[j].classList.remove('none');
                    }
                }
        
            });
        }   
    }
    
});
