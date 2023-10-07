import React, { useEffect, useState, } from 'react'
import { useNavigate, } from 'react-router-dom'
import { useDispatch, useSelector, } from 'react-redux'
import ReactPaginate from 'react-paginate'
import moment from 'moment'
import { login, } from '../../../redux/actions/authActions'
import { getUsers, } from '../../../redux/actions/usersActions'

import "./LoginComponent.css";

export default function HomeComponent() {
  const navigate = useNavigate()

  const [email, setEmail] = useState("jane@doe.com");
  const [password, setPassword] = useState("secret");

  const dispatch = useDispatch()
  const state = useSelector(state => ({
    auth: state.auth,
    users: state.users,
  }))

  useEffect(() => {}, [])

  useEffect(() => {
    if (state.auth.data !== null) {
        return navigate("/");
    }
  }, [state.auth,])

  const parseDate = date => moment(date).format('YYYY-MM-DD hh:mm')

  if (!state.auth.loading && typeof state.auth.data === 'object' && null !== state.auth.data) {
    console.log('authenticated', state.auth.data)
  }

  if (state.auth.loading || state.users.loading) {
    return <p>Loading...</p>
  }

    const onFormSubmit = (e) => {
        e.preventDefault();

        dispatch(login({ email, password, }));

        setEmail("");
        setPassword("");
    };

    const onEmailChange = (e) => {
        setEmail(e.target.value);
    };

    const onPasswordChange = (e) => {
        setPassword(e.target.value);
    };

  return (
    <>
        <div className='container login-container'>
            <div className="col-md-4 offset-md-4">
                <h3 className="lead">Login</h3>
                <form method="post" onSubmit={onFormSubmit}>
                    {state.auth.error ?
                        <div className="alert alert-warning alert-dismissible fade show" role="alert">
                            {state.auth.error}
                            <button type="button" className="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div> : null}
                    <div className="form-group">
                        <label htmlFor="email">Email</label>
                        <input 
                            name="email" 
                            className="form-control"
                            value={email}
                            onChange={onEmailChange}
                        />
                    </div>
                    <div className="form-group">
                        <label htmlFor="password">Password</label>
                        <input 
                            type="password"
                            name="password" 
                            className="form-control"
                            value={password}
                            onChange={onPasswordChange}
                        />
                    </div>
                    <input 
                        type="submit" 
                        className="btn btn-success" 
                    />
                </form>
            </div>
        </div>
    </>       
  )
}
