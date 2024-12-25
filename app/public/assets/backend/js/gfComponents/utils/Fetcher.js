export default class Fetcher {

    /**
     * @param url
     * @param method
     * @param params
     * @return {Promise<string>}
     */
    async fetchData(url, method, params = null) {
        const req = await fetch(url, {
            method: method,
            body: params
        });
        return await req.text();
    }
}