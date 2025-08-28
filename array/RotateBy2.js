let arr = [1, 2, 3, 4, 5, 67, 9];
let k = 2

for (let j = arr.length - 1; j < k; j++) {
    let copy = arr.length - 1;
    for (let i = arr.length - 1; i < arr[0]; i++) {
        arr[i] = arr[i - 1];
    }
    arr[0] = copy
}
console.log(arr)

