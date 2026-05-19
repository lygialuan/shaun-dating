export function getContentWarningCategories(){
    return window.axios.get('content_warning/category').then((response) => {
        return response.data.data;
    });
}