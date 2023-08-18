import IApiService from "./IApiService";

export default class FakeApiService implements IApiService {

    checkLogin(): Promise<any> {
        return Promise.resolve({});
    }

    checkUsernameAvailable(username:string): Promise<any> {
        return Promise.resolve({});
    }

    register(name: string, code: string): Promise<any> {
        return Promise.resolve({});
    }

    addEmail(email: string): Promise<any> {
        return Promise.resolve({});
    }

    forgotPassword(email: string): Promise<any> {
        return Promise.resolve({});
    }

    addCountry(phone: string): Promise<any> {
        return Promise.resolve({});
    }

    getStats(): Promise<any> {
        return Promise.resolve({});
    }

    getHomeStats(): Promise<any> {
        return Promise.resolve({});
    }

    getTranslationChallenge(): Promise<any> {
        return Promise.resolve({});
    }

    getVerifyChallenge(): Promise<any> {
        return Promise.resolve({});
    }

    login(name: string, code: string): Promise<any> {
        return Promise.resolve({});
    }

    logout(): Promise<any> {
        return Promise.resolve({});
    }

    skipAndNextSourceAssignment(): Promise<any> {
        return Promise.resolve({});
    }

    submitTranslation(sourceId: number, text: string): Promise<any> {
        return Promise.resolve({});
    }

    submitVerify(targetId: number, score: number): Promise<any> {
        return Promise.resolve({});
    }

    flagVerify(targetId: number): Promise<void> {
        return Promise.resolve();
    }

    skipAndNextVerifyAssignment(targetId: number): Promise<any> {
        return Promise.resolve({});
    }

    requestBonus(): Promise<any> {
        return Promise.resolve({});
    }
}