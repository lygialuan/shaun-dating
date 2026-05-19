export function getEditHistoriesList(type, itemId, page){
    return window.axios.get(`history/get/${type}/${itemId}/${page}`).then((response) => {
        return response.data.data;
    });
}