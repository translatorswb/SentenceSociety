export default interface IApiService {
    checkLogin(): Promise<any>;
    checkUsernameAvailable(username:string): Promise<any>;

    register(name: string, code: string, email: string, personalName:string, country: string): Promise<any>;

    addEmail(email: string): Promise<any>;

    forgotPassword(email: string): Promise<any>;

    addCountry(country: string): Promise<any>;

    getStats(): Promise<any>;

    getHomeStats(): Promise<any>;

    getTranslationChallenge(): Promise<any>;

    getVerifyChallenge(): Promise<any>;

    login(name: string, code: string): Promise<any>;

    logout(): Promise<any>;

    skipAndNextSourceAssignment(targetId?: number): Promise<any>;

    submitTranslation(sourceId: number, text: string): Promise<any>;

    submitVerify(targetId: number, score: number): Promise<any>;

    flagVerify(targetId?: number): Promise<any>;

    skipAndNextVerifyAssignment(targetId?: number): Promise<any> ;
    requestBonus(): Promise<any>;
}
