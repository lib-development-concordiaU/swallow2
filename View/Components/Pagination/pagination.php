<?php
include_once "Components/component.php";

class Pagination extends Component{
    
    public $total = 1;
    public $currentPage = 1;
    public $itemsPerPage = 15;
    public $template ='';
    public $templateButton = ''; 

    function __construct($in_total,$in_current,$in_itemsPerPage){
        $this->itemsPerPage = $in_itemsPerPage;
        $this->total = $in_total;
        $this->currentPage =  $in_current;
        $this->template = file_get_contents('Components/Pagination/template.html');
        $this->templateButton = file_get_contents('Components/Pagination/template-button.html');
    }

    public function render(){
        if($this->total > $this->itemsPerPage){
            $total_pages = ceil($this->total / $this->itemsPerPage );
        }else{
            $total_pages = 1;
        }
        
        if($this->currentPage > 1){
            $back = $this->currentPage - 1;
        }else{
            $back = 1;
        }
        
        if($this->currentPage >= $total_pages){
            $next = $total_pages;
        }else{
            $next = $this->currentPage + 1;
        }
        
        $html = $this->template;
        $html = str_replace("{back}",$back,$html);
        $html = str_replace("{next}",$next,$html);
        $html = str_replace("{current}",$this->currentPage,$html);
        $html = str_replace("{total}",$total_pages,$html);

        $pagesHtml = '';
        
        if($this->currentPage > 10){
            $start = $this->currentPage - 9;
        }else{
            $start = 1;
        }

        for ($i = $start; ($i <= $total_pages && $i < $start+10);$i++){
            $pageHtml = $this->templateButton;
            $pageHtml = str_replace('{page}',$i,$pageHtml);

            if($i == $this->currentPage){
                $pageHtml = str_replace('{active}','pagination-active',$pageHtml);  
            }else{
                $pageHtml = str_replace('{active}','p',$pageHtml); 
            }

            $pagesHtml .= $pageHtml;
        }   

        $html = str_replace('{pages}',$pagesHtml,$html);

        echo($html);
    }
}
?>