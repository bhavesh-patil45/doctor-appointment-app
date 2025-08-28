let arr = [10, 20, 30, 60, 50, 70]

let i = 0, j = arr.length - 1;

while (i < j) {

    let temp = arr[i];
    arr[i] = arr[j];
    arr[j] = temp

    i++;
    j--;

}
console.log(arr)