import * as React from "react";
import * as ReactDOM from "react-dom";

import './style/main.css';
import Main from "./components/Main";
import ApiService from "./service/ApiService";
import FakeApiService from "./service/FakeApiService";

if (process.env.NODE_ENV !== 'production') {
    console.log('FYI: we are not in production mode! (' + process.env.NODE_ENV + ')');
}

// API_ENDPOINT is supplied using webpack.DefinePlugin
const API_ENDPOINT: string = '';

const api = new ApiService(API_ENDPOINT);

const fakeApi = new FakeApiService();

ReactDOM.render(
    <Main apiService={api} />,
    document.getElementById("root")
);
