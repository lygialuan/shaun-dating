export function getReportCategoriesList(){
    return window.axios.get('report/category').then((response) => {
        return response.data.data;
    });
}
export function reportItem(reportData){
    return window.axios.post('report/store', reportData).then((response) => {
        return response.data.data;
    });
}