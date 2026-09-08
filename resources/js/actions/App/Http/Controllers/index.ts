import UserController from './UserController'
import CustomerController from './CustomerController'
import FileManagerController from './FileManagerController'
import Settings from './Settings'

const Controllers = {
    UserController: Object.assign(UserController, UserController),
    CustomerController: Object.assign(CustomerController, CustomerController),
    FileManagerController: Object.assign(FileManagerController, FileManagerController),
    Settings: Object.assign(Settings, Settings),
}

export default Controllers