type Pizza = {
  id: number;
  name: string;
  price: number;
};

// Define el tipo para el objeto en el array
type Order = {
  id: number;
  pizza: Array<Pizza>; // Cambia 'any' por el tipo real de los objetos de pizza
  status: 'ordered' | 'completed';
};

let cashInRegister = 100;
let nextOrderId = 1;
let nextPizzaId = 1;
const orderQueue: Order[] = [];

const menu: Pizza[] = [
  { id: nextPizzaId++, name: 'margherita', price: 20 },
  { id: nextPizzaId++, name: 'margherita', price: 20 },
  { id: nextPizzaId++, name: 'margherita', price: 20 },
  { id: nextPizzaId++, name: 'margherita', price: 20 },
  { id: nextPizzaId++, name: 'margherita', price: 20 },
  { id: nextPizzaId++, name: 'margherita', price: 20 },
  { id: nextPizzaId++, name: 'margherita', price: 20 },
  { id: nextPizzaId++, name: 'margherita', price: 20 },
];

// 1. Add new utility function addNewPizza that takes a pizza object and adds it to the menu

const addNewPizza = (pizzaObj: Omit<Pizza, 'id'>): Pizza => {
  const newPizza = {
    id: nextPizzaId++,
    ...pizzaObj
  }
  menu.push(newPizza);
  return newPizza;
};

// 2. Another utility function, placeOrder, that takes a pizza name parameter and: 1- finds that pizza object in the menu, 2- adds the income to the cashInRegister, 3-pushes a new order object to the orderQueue, 4. returns the new order object

const placeOrder = (pizzaName: string): Order => {
  const exists = menu.find((item) => item.name === pizzaName);

  if (exists) {
    cashInRegister += exists.price;
    const newOrder: Order = { id: nextOrderId++, pizza: [exists], status: 'ordered' };
    orderQueue.push(newOrder);
    return newOrder;
  } else {
    throw new Error(`Pizza ${pizzaName} not found in the menu.`);
  }
};

// 3. Add ID into the orderQueue, and then finds the correct order in the orderQueue and marks its statuts as "completed".

const completeOrder = (orderId: number): Order => {
  const order = orderQueue.find((order) => order.id === orderId);

  if (order) {
    order.status = 'completed';
  } else {
    throw new Error(`Imposible to complete the order`);
  }
  return order;
};

addNewPizza({ name: 'Tonno', price: 20 });
addNewPizza({ name: 'Peperonni', price: 10 });
addNewPizza({ name: 'Spinaci', price: 15 });

const order = placeOrder('Tonno');
console.log(`New order: ${order}`);
console.log(completeOrder(1));
console.log(`Menu: ${menu}`);
console.log(`Cash in register: ${cashInRegister}`);
console.log(`Order queue: ${orderQueue}`);

// 4. new function called getPizzaDetail, with one parameter, identifier. It would be a ID or name
const getPizzaDetail = (identifier: string | number): Pizza => {
  let pizza;
  if (typeof identifier === 'number') {
    pizza = menu.find((item) => item.id === identifier);
  } else if (typeof identifier === 'string') {
    pizza = menu.find((item) => item.name.toLowerCase() === identifier.toLowerCase());
  }

  if (!pizza) {
    throw new Error(`Pizza not found`);
  }

  return pizza;
};

console.log(getPizzaDetail(10));

type Address = {
  street?: string;
  city?: string;
  country?: string;
};

type Person = {
  name: string;
  age: number;
  isStudent: boolean;
  address?: Address;
};

const person1: Person = {
  name: 'Pedro',
  age: 9,
  isStudent: true,
};

const person2: Person = {
  name: 'Jill',
  age: 45,
  isStudent: false,
};

const displayInfo = (person) => {
  console.log(`Person info: ${person.name} lives at ${person.address.street}`);
};

console.log(displayInfo(person1));

const people: Array<Person> = [person1, person2];

type UserRole = 'guest' | 'member' | 'admin' | 'contributor';

type User = {
  id: number;
  username: string;
  role: UserRole;
};

type updatedUser = Partial<User>;
let nextUserId = 1;

const users: User[] = [
  { id: nextUserId++, username: 'john_doe', role: 'member' },
  { id: nextUserId++, username: 'jane_doe', role: 'admin' },
  { id: nextUserId++, username: 'guest_user', role: 'guest' },
];

const fethUserDetails = (username: string): User => {
  const user = users.find((user) => user.username === username);
  if (!user) {
    throw new Error(`User with username ${username} not found`);
  }

  return user;
};

console.log(fethUserDetails('john_doe'));

// find the user in the array by the id
// use object.assign to update the found user in place

const updateUser = (id: number, updates: updatedUser) => {
  const user = users.find((user) => user.id === id);
  if (!user) {
    throw new Error(`User with id ${id} not found`);
  } else {
    return Object.assign(user, updates);
  }
};

updateUser(1, { username: 'new_john_doe' });
updateUser(4, { role: 'contributor' });
//console.log(users)

const addNewUser = (newUser:Omit<User, 'id'>): User => {
  const user:User = { 
    id: nextUserId++,
    ...newUser,
  };
  
  users.push(user);

  return user;
};

addNewUser({ username: 'joe_schmoe', role: 'member' });

console.log(users);

const numeros = [10, 20, 30, 40, 50];
const frutas = ['manzana', 'banana', 'naranja', 'uva'];
const mezclado = [42, 'Hola', true, null, { nombre: 'Juan' }];

// generics
const getLastItem = <PlaceholderType>(array: PlaceholderType[]): PlaceholderType => {
  return array[array.length -1]
}

// Generics
function getLastItem2<T>(array: T[]): T {
  return array[array.length - 1];
};

console.log(getLastItem(numeros))
console.log(getLastItem(frutas))
console.log(getLastItem2(mezclado))