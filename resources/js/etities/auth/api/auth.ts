import api from "../../../app/api/api"

export const fetchVerifyCode = async (code: string, login: string) => {
    try {
        const response = await api.post('/email/verify', { code, login })
        console.log('verify: ', response)
        return response

    } catch (error) {
        console.error('Error verify:', error)
        throw error
        // return error.response
    }
}

export const fetchResendCode = async (login: string) => {
    try {
        const response = await api.post('/email/resend', { login })
        console.log('resend code: ', response.data)
        return response.data

    } catch (error) {
        console.error('Error resend code:', error.response)
        return error.response
    }
}
