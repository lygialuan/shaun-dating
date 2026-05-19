const API_URL = "https://g.tenor.com/v1/";
const API_KEY = "LIVDSRZULELA";
const LIMIT = "limit=12";
const LOCALE = "locale=cs_CZ";

const getTrendingGifs = (offset) => {
    let URL = `${API_URL}trending?key=${API_KEY}&${LOCALE}&${LIMIT}`;
    if (offset) URL += "&pos=" + offset;
    return fetch(URL);
};

const searchGifs = (value, offset) => {
    let URL = `${API_URL}search?key=${API_KEY}&q=${value}&${LOCALE}&${LIMIT}`;
    if (offset) URL += "&pos=" + offset;
    return fetch(URL);
};

const getApiData = async (value = null, offset) => {
    if (value) {
        return searchGifs(value, offset);
    } else {
        return getTrendingGifs(offset);
    }
};

export { getApiData };
