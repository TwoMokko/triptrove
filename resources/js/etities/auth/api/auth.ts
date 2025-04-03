import api from "../../../app/api/api";

export const fetchVerifyCode = async (code: string) => {
    try {
        const response = await api.post('/verify', { code })
        console.log('verify: ', response.data)
        return response.data

    } catch (error) {
        console.error('Error verify:', error.status)
        return error.status
    }
}

export const fetchResendCode = async () => {
    try {
        const response = await api.post('/resend')
        console.log('resend code: ', response.data)
        return response.data

    } catch (error) {
        console.error('Error resend code:', error)
        return error.status
    }
}
