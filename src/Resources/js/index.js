import {listToolbarActionRegistry} from 'sulu-admin-bundle/views';
import DuplicateContentAction from "./listToolbarActions/DuplicateContentAction";
import DuplicateFormAction from "./listToolbarActions/DuplicateFormAction";

listToolbarActionRegistry.add('Dupliquer', DuplicateFormAction);
listToolbarActionRegistry.add('Dupliquer', DuplicateContentAction);