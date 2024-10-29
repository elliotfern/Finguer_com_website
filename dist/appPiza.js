"use strict";
const menu = [
    { name: 'margherita', price: 20 },
    { name: 'margherita', price: 20 },
    { name: 'margherita', price: 20 },
    { name: 'margherita', price: 20 },
    { name: 'margherita', price: 20 },
    { name: 'margherita', price: 20 },
    { name: 'margherita', price: 20 },
    { name: 'margherita', price: 20 },
];
let cashInRegister = 100;
let nextOrderId = 1;
const orderQueue = [];
// 1. Add new utility function addNewPizza that takes a pizza object and adds it to the menu
const addNewPizza = (pizzaObj) => {
    menu.push(pizzaObj);
};
// 2. Another utility function, placeOrder, that takes a pizza name parameter and: 1- finds that pizza object in the menu, 2- adds the income to the cashInRegister, 3-pushes a new order object to the orderQueue, 4. returns the new order object
const placeOrder = (pizzaName) => {
    const exists = menu.find((item) => item.name === pizzaName);
    if (exists) {
        cashInRegister += exists.price;
        const newOrder = { id: nextOrderId++, pizza: [exists], status: 'ordered' };
        orderQueue.push(newOrder);
        return newOrder;
    }
    else {
        throw new Error(`Pizza ${pizzaName} not found in the menu.`);
    }
};
// 3. Add ID into the orderQueue, and then finds the correct order in the orderQueue and marks its statuts as "completed".
const completeOrder = (orderId) => {
    const order = orderQueue.find((order) => order.id === orderId);
    if (order) {
        order.status = 'completed';
    }
    else {
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
const person1 = {
    name: "Pedro",
    age: 9,
    isStudent: true
};
const person2 = {
    name: "Jill",
    age: 45,
    isStudent: false
};
const displayInfo = (person) => {
    console.log(`Person info: ${person.name} lives at ${person.address.street}`);
};
console.log(displayInfo(person1));
const people = [person1, person2];
