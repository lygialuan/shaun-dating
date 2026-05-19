export function getCountriesList(){
    return window.axios.get(`/country/get`).then((response) => {
        return response.data.data;
    });
}
export function getStatesListByCountry(countryId){
    return window.axios.get(`/country/get_state/${countryId}`).then((response) => {
        return response.data.data;
    });
}
export function getCitesListByState(stateId){
    return window.axios.get(`/country/get_city/${stateId}`).then((response) => {
        return response.data.data;
    });
}