<?php
/*use App\SubjectCategory;
if (! function_exists('getsubjects')) {
function getsubjets(){
    return SubjectCategory::select('title')->get();
}
}*/
echo env('DB_DATABASE');
if (! function_exists('getMessage')) {
    function getMessage(){
        echo "Successfully Worked";
    }
}