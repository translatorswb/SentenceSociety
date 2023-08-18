import IApiService from "./IApiService";

export default class ApiService implements IApiService {

    private static PROFILE_URL = '/api/profile';
    private static CHECK_USERNAME_AVAILABLE_URL = '/api/checkname';
    private static LOGIN_URL = '/api/login';
    private static LOGOUT_URL = '/api/logout';
    private static REGISTER_URL = '/api/register';
    private static REGISTER_ADD_EMAIL_URL = '/api/addemail';
    private static FORGOT_PASSWORD_URL = '/api/forgotpassword';
    private static REGISTER_SELECT_COUNTRY_URL = '/api/addcountry';
    private static RANDOM_STATS_URL = '/api/stats/random';
    private static HOME_STATS_URL = '/api/stats/home';
    private static NEXT_TRANSLATION_URL = '/api/source/next';
    private static SKIP_NEXT_TRANSLATION_URL = '/api/source/{sourceId}/skip_next';
    private static SUBMIT_TRANSLATION_URL = '/api/source/{sourceId}/target';
    private static NEXT_VERIFY_URL = '/api/target/next';
    private static SUBMIT_VERIFY_URL = '/api/target/{targetId}/rating';
    private static FLAG_VERIFY_URL = '/api/target/{targetId}/flag';
    private static SKIP_NEXT_VERIFY_URL = '/api/target/{targetId}/skip_next';
    private static REQUEST_BONUS_URL = '/api/requestbonus';

    constructor(private endPoint: string) {

        // console.log('api endpoint:', endPoint)
    }
    private get<T>(url:string): Promise<T> {
        return fetch(url, {
            method: 'GET',
            credentials: 'include',
        })
            .then((response: Response) => {
                if (!response.ok) {
                    throw new Error(response.statusText)
                }
                // return response.json<{ data: T }>()
                return response.json()
            })
            .then(data => { /* <-- data inferred as { data: T }*/
                return data.data
            })
    }

    // fetch options:
    //     method: "POST", // *GET, POST, PUT, DELETE, etc.
    //     mode: "cors", // no-cors, cors, *same-origin
    //     cache: "no-cache", // *default, no-cache, reload, force-cache, only-if-cached
    //     credentials: "same-origin", // include, *same-origin, omit
    //     headers: {
    //         "Content-Type": "application/json; charset=utf-8",
    //         // "Content-Type": "application/x-www-form-urlencoded",
    //     },
    //     redirect: "follow", // manual, *follow, error
    //     referrer: "no-referrer", // no-referrer, *client
    //     body: JSON.stringify(data), // body data type must match "Content-Type" header

    private post<T>(url:string, data: any): Promise<T> {
        return fetch(url,
            {
                    method: "POST",
                    body: JSON.stringify(data),
                    credentials: 'include',
                })
            .then((response: Response) => {
                if (!response.ok) {
                    return {data: { error: true, code: response.status, message: response.statusText }}
                }
                // return response.json<{ data: T }>()
                return response.json()
            })
            .then(data => { /* <-- data inferred as { data: T }*/
                return data.data
            })
    }

    checkLogin(): Promise<any> {
        return this.get<{ title: string; message: string }>(this.endPoint + ApiService.PROFILE_URL)
            .then(data => {
                return data;
            })
    }

    checkUsernameAvailable(username:string): Promise<any> {
        return this.post<{available: boolean}>(this.endPoint + ApiService.CHECK_USERNAME_AVAILABLE_URL, {name: username})
            .then(data => {
                return data.available;
            })
    }

    register(name: string, code: string, email: string, personalName:string, country: string): Promise<any> {
        return this.post<{ name: string }>(this.endPoint + ApiService.REGISTER_URL, {name, code, email, personalName, country})
            .then(data => {
                return data;
            })
    }

    addEmail(email: string): Promise<any> {
        return this.post<boolean>(this.endPoint + ApiService.REGISTER_ADD_EMAIL_URL, {email})
            .then(data => {
                return data;
            })
    }

    forgotPassword(email: string): Promise<any> {
        return this.post<boolean>(this.endPoint + ApiService.FORGOT_PASSWORD_URL, {email})
            .then(data => {
                return data;
            })
    }

    addCountry(country: string): Promise<any> {
        return this.post<boolean>(this.endPoint + ApiService.REGISTER_SELECT_COUNTRY_URL, {country})
            .then(data => {
                return data;
            })
    }

    getStats(): Promise<any> {
        return this.get<{ stats: any}>(this.endPoint + ApiService.RANDOM_STATS_URL)
            .then(data => {
                return data;
            })
    }

    getHomeStats(): Promise<any> {
        return this.get<{ stats: any}>(this.endPoint + ApiService.HOME_STATS_URL)
            .then(data => {
                return data;
            })
    }

    login(name: string, code: string): Promise<any> {
        return this.post<{ name: string }>(this.endPoint + ApiService.LOGIN_URL, {name, code})
            .then(data => {
                return data;
            })
    }

    logout(): Promise<any> {
        return this.post<{ name: string }>(this.endPoint + ApiService.LOGOUT_URL, {})
            .then(data => {
                return data;
            })
    }

    getTranslationChallenge(): Promise<any> {
        return this.post<{ id: number, text: string }>(this.endPoint + ApiService.NEXT_TRANSLATION_URL, {})
            .then(data => {
                return data;
            })
    }

    getVerifyChallenge(): Promise<any> {
        return this.post<{ id: number, text: string, source: string }>(this.endPoint + ApiService.NEXT_VERIFY_URL, {})
            .then(data => {
                return data;
            })
    }

    skipAndNextSourceAssignment(sourceId: number): Promise<any> {
        return this.post<{ id: number, text: string }>(this.endPoint + ApiService.SKIP_NEXT_TRANSLATION_URL.replace('{sourceId}', sourceId.toString(10)), {})
            .then(data => {
                return data;
            })
    }

    submitTranslation(sourceId: number, text: string): Promise<any> {
        return this.post<{ id: number }>(this.endPoint + ApiService.SUBMIT_TRANSLATION_URL.replace('{sourceId}', sourceId.toString(10)), {
            text: text
        })
            .then(data => {
                return data;
            })
    }

    submitVerify(targetId: number, score: number): Promise<any> {
        return this.post<{ id: number }>(this.endPoint + ApiService.SUBMIT_VERIFY_URL.replace('{targetId}', targetId.toString(10)), {
            rating: score
        })
            .then(data => {
                return data;
            })
    }

    flagVerify(targetId: number): Promise<void> {
        return this.post<void>(this.endPoint + ApiService.FLAG_VERIFY_URL.replace('{targetId}', targetId.toString(10)), {})
            .then(data => {
                return data;
            })
    }

    skipAndNextVerifyAssignment(targetId: number): Promise<any> {
        return this.post<{ id: number, text: string, source: string }>(this.endPoint + ApiService.SKIP_NEXT_VERIFY_URL.replace('{targetId}', targetId.toString(10)), {})
            .then(data => {
                return data;
            })
    }

    requestBonus(): Promise<any> {
        return this.post<void>(this.endPoint + ApiService.REQUEST_BONUS_URL, {})
            .then(data => {
                return data;
            })
    }

}

